import os
import numpy as np
import pandas as pd
import oracledb
import datetime
from underthesea import word_tokenize
from sentence_transformers import SentenceTransformer
from sentence_transformers.util import cos_sim
from flask import Flask, jsonify, request
from flask_cors import CORS
import requests
import traceback
import random

DB_HOST = os.environ.get("DB_HOST", "localhost")
DB_PORT = int(os.environ.get("DB_PORT", 1521))
DB_SERVICE_NAME = os.environ.get("DB_SERVICE_NAME", "QUANLYKHOAHOC")
DB_USER = os.environ.get("DB_USER", "duy_admin")
DB_PASSWORD = os.environ.get("DB_PASSWORD", "duyadmin")

VIETNAMESE_STOP_WORDS = ["khóa học", "khóa_học", "bài học", "học", "và", "là", "của", "các", "cho", "đến", "từ", "này", "để", "trong", "một", "với", "về"]
COURSE_API_BASE_URL = "http://localhost/CoursePro1/api/course_api.php"
ALL_COURSES_API_URL = "http://localhost/CoursePro1/api/course_api.php?isGetAllCourse=true&option=3"
SENTENCE_TRANSFORMER_MODEL_NAME = 'intfloat/multilingual-e5-large-instruct'

all_courses_df_processed = None
sentence_transformer_model_global = None
all_course_embeddings_global = None
is_data_loaded = False

def get_detailed_instruct(task_description: str, query: str) -> str:
    return f'Instruct: {task_description}\nQuery: {query}'

def tokenize_text_vietnamese_underthesea(text):
    if not isinstance(text, str):
        return ""
    try:
        tokens = word_tokenize(text)
        filtered_tokens = [token for token in tokens if token.lower() not in VIETNAMESE_STOP_WORDS]
        return " ".join(filtered_tokens)
    except Exception as e:
        print(f"Lỗi khi tokenize với underthesea: {e}. Sử dụng split().")
        try:
            tokens = str(text).split()
            filtered_tokens = [token for token in tokens if token.lower() not in VIETNAMESE_STOP_WORDS]
            return " ".join(filtered_tokens)
        except:
             return str(text)

def fetch_courses_from_oracle():
    dsn = f"{DB_HOST}:{DB_PORT}/{DB_SERVICE_NAME}"
    connection = None
    cursor = None
    courses_data = []
    print(f"Đang kết nối đến Oracle Database với DSN: {dsn} và người dùng: {DB_USER}...")
    try:
        connection = oracledb.connect(user=DB_USER, password=DB_PASSWORD, dsn=dsn)
        print("Kết nối Oracle thành công!")
        cursor = connection.cursor()
        query_courses = "SELECT COURSEID, TITLE, DESCRIPTION FROM Course"
        cursor.execute(query_courses)
        main_course_columns = [desc[0] for desc in cursor.description]

        temp_course_list = []
        for row in cursor:
            processed_row = {}
            for i, col_name in enumerate(main_course_columns):
                item = row[i]
                if isinstance(item, oracledb.LOB):
                    processed_row[col_name] = item.read() if item else None
                else:
                    processed_row[col_name] = item
            temp_course_list.append(processed_row)

        for course_info in temp_course_list:
            current_course_id = course_info['COURSEID']
            objectives_texts = []
            query_objectives = "SELECT Objective FROM CourseObjective WHERE CourseID = :course_id_bv"
            cursor_objectives = connection.cursor()
            cursor_objectives.execute(query_objectives, course_id_bv=current_course_id)
            for obj_row in cursor_objectives:
                if obj_row[0]: objectives_texts.append(str(obj_row[0]))
            course_info['objectives_text'] = " ".join(objectives_texts)
            cursor_objectives.close()

            requirements_texts = []
            query_requirements = "SELECT Requirement FROM CourseRequirement WHERE CourseID = :course_id_bv"
            cursor_requirements = connection.cursor()
            cursor_requirements.execute(query_requirements, course_id_bv=current_course_id)
            for req_row in cursor_requirements:
                if req_row[0]: requirements_texts.append(str(req_row[0]))
            course_info['requirements_text'] = " ".join(requirements_texts)
            cursor_requirements.close()

            courses_data.append(course_info)

        if not courses_data:
            print("Không có dữ liệu khóa học nào trong bảng Course.")
            return None

        df = pd.DataFrame(courses_data)
        df.rename(columns={
            'COURSEID': 'course_id', 'TITLE': 'title', 'DESCRIPTION': 'description',
            'objectives_text': 'objectives', 'requirements_text': 'requirements'
            }, inplace=True)
        print(f"Đã tải {len(df)} khóa học (bao gồm objectives, requirements) từ Oracle DB.")
        return df
    except oracledb.Error as e:
        error_obj, = e.args
        print(f"Lỗi Oracle khi truy vấn dữ liệu: {error_obj.message} (Code: {error_obj.code})")
        return None
    except Exception as e:
        print(f"Đã xảy ra lỗi không xác định khi lấy dữ liệu từ Oracle: {e}")
        print(traceback.format_exc())
        return None
    finally:
        if cursor: cursor.close()
        if connection: connection.close()

def preprocess_data_from_db(db_data_df):
    if db_data_df is None or db_data_df.empty:
        print("Không có dữ liệu từ DB để tiền xử lý.")
        return None
    expected_cols = ['course_id', 'title', 'description', 'objectives', 'requirements']
    for col in expected_cols:
        if col not in db_data_df.columns: db_data_df[col] = ''
    data = db_data_df[expected_cols].copy()
    data['title'] = data['title'].astype(str).str.replace(':', '', regex=False).str.replace(',,', ',', regex=False).str.strip()
    data['description'] = data['description'].astype(str).str.replace('_', ' ', regex=False).str.replace(':', '', regex=False).str.replace(r'[()]', '', regex=True).str.strip()
    data['objectives'] = data['objectives'].astype(str).str.strip()
    data['requirements'] = data['requirements'].astype(str).str.strip()
    data['tags'] = (data['title'].fillna('') + ' ' + data['description'].fillna('') + ' ' +
                    data['objectives'].fillna('') + ' ' + data['requirements'].fillna(''))
    data['tags'] = data['tags'].str.strip().str.lower()
    data['processed_tags_for_embedding'] = data['tags'].apply(tokenize_text_vietnamese_underthesea)
    print("Tiền xử lý dữ liệu khóa học (tạo tags và processed_tags_for_embedding) hoàn tất.")
    return data

def generate_course_embeddings_global(feature_df, model):
    if feature_df is None or 'processed_tags_for_embedding' not in feature_df.columns or feature_df['processed_tags_for_embedding'].isnull().all():
        print("Lỗi: Feature DataFrame không hợp lệ hoặc cột 'processed_tags_for_embedding' trống để tạo embeddings.")
        return None

    documents_to_encode = feature_df['processed_tags_for_embedding'].tolist()

    print(f"Đang tạo embeddings cho {len(documents_to_encode)} khóa học...")
    try:
        embeddings = model.encode(documents_to_encode, convert_to_tensor=True, normalize_embeddings=True, show_progress_bar=True)
        print("Tạo embeddings cho tất cả khóa học hoàn tất.")
        return embeddings
    except Exception as e:
        print(f"Lỗi trong quá trình tạo embeddings: {e}")
        print(traceback.format_exc())
        return None

def fetch_purchase_history(user_id):
    purchased_course_ids = set()
    order_api_url = f"http://localhost/CoursePro1/api/order_api.php?userID={user_id}"
    try:
        order_response = requests.get(order_api_url, timeout=10)
        order_response.raise_for_status()
        orders_data = order_response.json()
        if orders_data.get("success") and orders_data.get("data"):
            for order in orders_data["data"]:
                order_id = order.get("orderID")
                if order_id:
                    order_detail_api_url = f"http://localhost/CoursePro1/api/order_detail_api.php?orderID={order_id}"
                    try:
                        detail_response = requests.get(order_detail_api_url, timeout=10)
                        detail_response.raise_for_status()
                        details_data = detail_response.json()
                        if details_data.get("success") and details_data.get("data"):
                            for item in details_data["data"]:
                                course_id = item.get("courseID")
                                if course_id: purchased_course_ids.add(course_id)
                    except Exception as e_detail: print(f"Lỗi gọi API chi tiết đơn hàng cho {order_id}: {e_detail}")
    except Exception as e_order:
        print(f"Lỗi gọi API đơn hàng cho userID {user_id}: {e_order}")
        return []
    print(f"Lịch sử mua hàng cho userID {user_id}: {list(purchased_course_ids)}")
    return list(purchased_course_ids)

def fetch_course_details_php(course_id):
    if not course_id: return None
    course_detail_url = f"{COURSE_API_BASE_URL}?courseID={course_id}&isGetCourseForRecommend=true"
    try:
        response = requests.get(course_detail_url, timeout=5)
        response.raise_for_status()
        data = response.json()
        if data.get("success") and data.get("data"): return data["data"]
        return None
    except Exception: return None

def fetch_all_courses_for_generic_recommendation():
    print(f"Fetching all courses for generic recommendations from: {ALL_COURSES_API_URL}")
    try:
        response = requests.get(ALL_COURSES_API_URL, timeout=15)
        response.raise_for_status()
        data = response.json()
        if data.get("success") and isinstance(data.get("data"), list):
            print(f"Đã tải {len(data['data'])} khóa học cho gợi ý chung.")
            return data["data"]
        return []
    except Exception as e:
        print(f"Lỗi khi gọi PHP API lấy tất cả khóa học: {e}")
        return []

def get_recommendations_from_history_ids(history_ids_list, model, feature_df_global, all_course_embeds_g, top_n=10):
    if not history_ids_list:
        print("Lịch sử xem (mua) trống.")
        return []
    if model is None or feature_df_global is None or all_course_embeds_g is None:
        print("Lỗi: Model hoặc dữ liệu nền (features, embeddings) chưa được tải.")
        return []

    history_tags_list = []
    valid_history_ids = []
    for hist_course_id in history_ids_list:
        course_data_for_tags = feature_df_global[feature_df_global['course_id'] == hist_course_id]
        if not course_data_for_tags.empty:
            history_tags_list.append(course_data_for_tags['processed_tags_for_embedding'].iloc[0])
            valid_history_ids.append(hist_course_id)

    if not history_tags_list:
        print("Không tìm thấy tags hợp lệ cho các khóa học trong lịch sử.")
        return []

    history_profile_text = " ".join(history_tags_list)
    if not history_profile_text.strip():
        print("Hồ sơ lịch sử người dùng trống sau khi xử lý.")
        return []

    query_instruction = "Recommend courses based on the following topics and descriptions"
    query_for_embedding = get_detailed_instruct(query_instruction, history_profile_text)

    print(f"Encoding history profile query: {query_for_embedding[:200]}...")
    try:
        history_embedding = model.encode([query_for_embedding], convert_to_tensor=True, normalize_embeddings=True)
    except Exception as e:
        print(f"Lỗi khi tạo embedding cho hồ sơ lịch sử: {e}")
        return []

    cos_scores = cos_sim(history_embedding, all_course_embeds_g)[0]

    scores_with_indices = []
    for i, score in enumerate(cos_scores.tolist()):
        scores_with_indices.append({'index': i, 'score': score})

    sorted_scores = sorted(scores_with_indices, key=lambda x: x['score'], reverse=True)

    recommended_courses_details_list = []
    count = 0
    for item in sorted_scores:
        idx = item['index']
        score = item['score']
        course_id_at_index = feature_df_global.iloc[idx]['course_id']

        if course_id_at_index in valid_history_ids:
            continue

        if count < top_n:
            course_details = fetch_course_details_php(course_id_at_index)
            if course_details:
                course_details["recommendation_score"] = float(score)
                recommended_courses_details_list.append(course_details)
                count += 1
        else:
            break

    return recommended_courses_details_list

app = Flask(__name__)
CORS(app)

def load_initial_data():
    global all_courses_df_processed, sentence_transformer_model_global, all_course_embeddings_global, is_data_loaded
    if is_data_loaded:
        print("Dữ liệu đã được tải trước đó.")
        return
    print("Đang tải dữ liệu ban đầu cho ứng dụng Flask...")

    try:
        print(f"Đang tải mô hình Sentence Transformer: {SENTENCE_TRANSFORMER_MODEL_NAME}...")
        sentence_transformer_model_global = SentenceTransformer(SENTENCE_TRANSFORMER_MODEL_NAME)
        print("Tải mô hình Sentence Transformer thành công!")
    except Exception as e:
        print(f"Lỗi khi tải mô hình Sentence Transformer: {e}")
        print(traceback.format_exc())
        is_data_loaded = False
        return

    raw_df = fetch_courses_from_oracle()
    if raw_df is not None and not raw_df.empty:
        all_courses_df_processed = preprocess_data_from_db(raw_df)
        if all_courses_df_processed is not None and not all_courses_df_processed.empty:
            all_course_embeddings_global = generate_course_embeddings_global(all_courses_df_processed, sentence_transformer_model_global)
            if all_course_embeddings_global is not None:
                is_data_loaded = True
                print("Tải và tiền xử lý dữ liệu, tạo embeddings ban đầu thành công!")
            else: print("Lỗi: Không thể tạo course embeddings.")
        else: print("Lỗi: Tiền xử lý dữ liệu thất bại.")
    else: print("Lỗi: Không thể tải dữ liệu từ Oracle DB.")

with app.app_context():
    load_initial_data()

@app.route('/recommend/<string:user_id>', methods=['GET'])
def recommend_for_user(user_id):
    global all_courses_df_processed, sentence_transformer_model_global, all_course_embeddings_global, is_data_loaded

    if not is_data_loaded or sentence_transformer_model_global is None or all_course_embeddings_global is None:
        print("Dữ liệu hoặc mô hình chưa sẵn sàng, đang thử tải lại...")
        load_initial_data()
        if not is_data_loaded or sentence_transformer_model_global is None or all_course_embeddings_global is None:
            return jsonify({"success": False, "message": "Lỗi máy chủ: Dữ liệu gợi ý hoặc mô hình chưa sẵn sàng.", "data": None}), 500

    if not user_id:
        return jsonify({"success": False, "message": "UserID không được cung cấp.", "data": None}), 400

    try:
        purchase_history_ids = fetch_purchase_history(user_id)

        if not purchase_history_ids:
            print(f"Không có lịch sử mua hàng cho userID {user_id}. Cung cấp gợi ý chung/ngẫu nhiên.")
            all_php_courses = fetch_all_courses_for_generic_recommendation()
            generic_recommendations = []
            if all_php_courses:
                num_to_recommend = min(10, len(all_php_courses))
                randomly_selected_courses = random.sample(all_php_courses, num_to_recommend)
                for course_detail in randomly_selected_courses:
                    course_detail["recommendation_score"] = 0.0
                    generic_recommendations.append(course_detail)
            if generic_recommendations:
                 return jsonify({"success": True, "message": "Không có lịch sử mua hàng, đây là một số khóa học ngẫu nhiên.", "data": generic_recommendations}), 200
            else:
                 return jsonify({"success": True, "message": "Không có lịch sử mua hàng và không thể tạo gợi ý chung/ngẫu nhiên.", "data": []}), 200

        recommendations = get_recommendations_from_history_ids(
            purchase_history_ids,
            sentence_transformer_model_global,
            all_courses_df_processed,
            all_course_embeddings_global,
            top_n=10
        )

        if recommendations:
            return jsonify({"success": True, "message": "Lấy danh sách gợi ý thành công.", "data": recommendations}), 200
        else:
            return jsonify({"success": True, "message": "Không tìm thấy gợi ý mới dựa trên lịch sử mua hàng của bạn.", "data": []}), 200

    except Exception as e:
        print(f"Lỗi không mong muốn xảy ra khi tạo gợi ý cho userID {user_id}: {e}")
        print(traceback.format_exc())
        return jsonify({"success": False, "message": f"Lỗi máy chủ: {str(e)}", "data": None}), 500

@app.route('/recommend_course/<string:course_id_param>', methods=['GET'])
def recommend_for_course(course_id_param):
    global all_courses_df_processed, sentence_transformer_model_global, all_course_embeddings_global, is_data_loaded

    if not is_data_loaded or sentence_transformer_model_global is None or all_course_embeddings_global is None:
        print("Dữ liệu hoặc mô hình chưa sẵn sàng, đang thử tải lại...")
        load_initial_data()
        if not is_data_loaded or sentence_transformer_model_global is None or all_course_embeddings_global is None:
            return jsonify({"success": False, "message": "Lỗi máy chủ: Dữ liệu gợi ý hoặc mô hình chưa sẵn sàng.", "data": None}), 500

    if not course_id_param:
        return jsonify({"success": False, "message": "CourseID không được cung cấp.", "data": None}), 400

    if all_courses_df_processed is None:
        return jsonify({"success": False, "message": "Lỗi máy chủ: Dữ liệu khóa học chưa được xử lý.", "data": None}), 500

    target_course_data = all_courses_df_processed[all_courses_df_processed['course_id'] == course_id_param]
    if target_course_data.empty:
        return jsonify({"success": False, "message": f"Khóa học với ID '{course_id_param}' không có trong dữ liệu để tạo gợi ý.", "data": None}), 404

    target_course_index = target_course_data.index[0]
    target_course_tag_for_embedding = target_course_data['processed_tags_for_embedding'].iloc[0]

    query_instruction_item = "Find courses similar to this course"
    query_for_item_embedding = get_detailed_instruct(query_instruction_item, target_course_tag_for_embedding)

    try:
        target_course_embedding = sentence_transformer_model_global.encode([query_for_item_embedding], convert_to_tensor=True, normalize_embeddings=True)
    except Exception as e:
        print(f"Lỗi khi tạo embedding cho khóa học mục tiêu {course_id_param}: {e}")
        return jsonify({"success": False, "message": f"Lỗi khi xử lý khóa học mục tiêu.", "data": None}), 500

    cos_scores_item = cos_sim(target_course_embedding, all_course_embeddings_global)[0]

    scores_with_indices_item = []
    for i, score in enumerate(cos_scores_item.tolist()):
         scores_with_indices_item.append({'index': i, 'score': score})

    sorted_scores_item = sorted(scores_with_indices_item, key=lambda x: x['score'], reverse=True)

    recommended_courses_details_list = []
    count = 0
    top_n = 5
    for item in sorted_scores_item:
        idx = item['index']
        score_val = item['score']
        if idx == target_course_index:
            continue

        if count < top_n:
            rec_course_id = all_courses_df_processed.iloc[idx]['course_id']
            course_details = fetch_course_details_php(rec_course_id)
            if course_details:
                course_details["recommendation_score"] = float(score_val)
                recommended_courses_details_list.append(course_details)
                count += 1
        else:
            break

    if recommended_courses_details_list:
        return jsonify({"success": True, "message": "Lấy gợi ý cho khóa học thành công.", "data": recommended_courses_details_list}), 200
    else:
        return jsonify({"success": True, "message": f"Không tìm thấy gợi ý mới cho khóa học ID '{course_id_param}'.", "data": []}), 200

if __name__ == "__main__":
    print("Khởi chạy Flask API server...")
    app.run(debug=True, host='0.0.0.0', port=5001)
