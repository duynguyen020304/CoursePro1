import os
import numpy as np
import pandas as pd
import oracledb
import datetime
from underthesea import word_tokenize
from sklearn.feature_extraction.text import CountVectorizer
from sklearn.metrics.pairwise import cosine_similarity
from flask import Flask, jsonify, request
from flask_cors import CORS
import requests
import traceback

DB_HOST = os.environ.get("DB_HOST", "localhost")
DB_PORT = int(os.environ.get("DB_PORT", 1521))
DB_SERVICE_NAME = os.environ.get("DB_SERVICE_NAME", "QUANLYKHOAHOC")
DB_USER = os.environ.get("DB_USER", "duy_admin")
DB_PASSWORD = os.environ.get("DB_PASSWORD", "duyadmin")

VIETNAMESE_STOP_WORDS = ["khóa học", "khóa_học", "bài học", "học", "và", "là", "của", "các", "cho", "đến", "từ", "này", "để", "trong", "một", "với", "về"]
COURSE_API_BASE_URL = "http://localhost/CoursePro1/api/course_api.php"

all_courses_df_processed = None
similarity_matrix_global = None
cv_vectorizer_global = None
all_course_vectors_global = None
is_data_loaded = False

def tokenize_text_vietnamese_underthesea(text):
    if not isinstance(text, str):
        return ""
    try:
        tokens = word_tokenize(text)
        return " ".join(tokens)
    except Exception as e:
        print(f"Lỗi khi tokenize với underthesea: {e}. Sử dụng split().")
        return " ".join(str(text).split())

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
        query = "SELECT COURSEID, TITLE, DESCRIPTION FROM Course"
        cursor.execute(query)
        colnames = [desc[0] for desc in cursor.description]
        for row in cursor:
            processed_row = {}
            for i, col_name in enumerate(colnames):
                item = row[i]
                if isinstance(item, oracledb.LOB):
                    processed_row[col_name] = item.read() if item else None
                else:
                    processed_row[col_name] = item
            courses_data.append(processed_row)
        if not courses_data:
            print("Không có dữ liệu khóa học nào trong bảng Course.")
            return None
        df = pd.DataFrame(courses_data)
        df.rename(columns={'COURSEID': 'course_id', 'TITLE': 'title', 'DESCRIPTION': 'description'}, inplace=True)
        print(f"Đã tải {len(df)} khóa học (dữ liệu cơ bản) từ Oracle DB.")
        return df
    except oracledb.Error as e:
        error_obj, = e.args
        print(f"Lỗi Oracle khi truy vấn bảng Course: {error_obj.message} (Code: {error_obj.code})")
        return None
    except Exception as e:
        print(f"Đã xảy ra lỗi không xác định khi lấy dữ liệu từ Oracle: {e}")
        return None
    finally:
        if cursor: cursor.close()
        if connection: connection.close()

def preprocess_data_from_db(db_data_df):
    if db_data_df is None or db_data_df.empty:
        print("Không có dữ liệu từ DB để tiền xử lý.")
        return None
    data = db_data_df[['course_id', 'title', 'description']].copy()
    data['title'] = data['title'].astype(str).str.replace(':', '', regex=False).str.replace(',,', ',', regex=False).str.strip()
    data['description'] = data['description'].astype(str).str.replace('_', ' ', regex=False).str.replace(':', '', regex=False).str.replace(r'[()]', '', regex=True).str.strip()
    data['tags'] = (data['title'].fillna('') + ' ' + data['description'].fillna(''))
    data['tags'] = data['tags'].str.strip().str.lower()
    data['tags'] = data['tags'].apply(tokenize_text_vietnamese_underthesea)
    print("Tiền xử lý dữ liệu khóa học (tạo tags) hoàn tất.")
    return data

def calculate_similarity_matrix_global(feature_df):
    if feature_df is None or 'tags' not in feature_df.columns or feature_df['tags'].isnull().all() or feature_df['tags'].eq('').all():
        print("Lỗi: Feature DataFrame không hợp lệ hoặc cột 'tags' trống.")
        return None, None, None

    cv = CountVectorizer(max_features=10000, stop_words=VIETNAMESE_STOP_WORDS)
    try:
        vectors = cv.fit_transform(feature_df['tags']).toarray()
        similarity_matrix = cosine_similarity(vectors)
        print("Tính toán ma trận tương đồng hoàn tất.")
        return similarity_matrix, cv, vectors
    except Exception as e:
        print(f"Lỗi trong quá trình vector hóa hoặc tính toán sự tương đồng: {e}")
        return None, None, None

def fetch_purchase_history(user_id):
    purchased_course_ids = set()
    order_api_url = f"http://localhost/CoursePro1/api/order_api.php?userID={user_id}"
    print(f"Fetching orders for userID: {user_id} from {order_api_url}")
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
                                if course_id:
                                    purchased_course_ids.add(course_id)
                    except Exception as e_detail:
                        print(f"Lỗi gọi API chi tiết đơn hàng cho {order_id}: {e_detail}")
    except Exception as e_order:
        print(f"Lỗi gọi API đơn hàng cho userID {user_id}: {e_order}")
        return []
    print(f"Lịch sử mua hàng cho userID {user_id}: {list(purchased_course_ids)}")
    return list(purchased_course_ids)

def fetch_course_details_php(course_id):
    if not course_id:
        return None
    course_detail_url = f"{COURSE_API_BASE_URL}?courseID={course_id}&isGetCourseForRecommend=true"
    try:
        response = requests.get(course_detail_url, timeout=5)
        response.raise_for_status()
        data = response.json()
        if data.get("success") and data.get("data"):
            return data["data"]
        else:
            print(f"PHP API call for course details {course_id} failed or returned no data: {data.get('message')}")
            return None
    except requests.exceptions.RequestException as e:
        print(f"Lỗi khi gọi PHP API chi tiết khóa học cho {course_id}: {e}")
        return None
    except ValueError as e_json:
        print(f"Lỗi giải mã JSON từ PHP API chi tiết khóa học cho {course_id}: {e_json}")
        return None

def get_recommendations_from_history_ids(history_ids_list, feature_df_global, all_course_vectors_g, vectorizer_g, similarity_matrix_g, top_n=10):
    if not history_ids_list:
        print("Lịch sử xem (mua) trống.")
        return []
    if feature_df_global is None or all_course_vectors_g is None or vectorizer_g is None or similarity_matrix_g is None:
        print("Lỗi: Dữ liệu nền (features, vectors, vectorizer, similarity matrix) chưa được tải.")
        return []

    history_tags_list = []
    valid_history_ids = []
    for hist_course_id in history_ids_list:
        course_data_for_tags = feature_df_global[feature_df_global['course_id'] == hist_course_id]
        if not course_data_for_tags.empty:
            history_tags_list.append(course_data_for_tags['tags'].iloc[0])
            valid_history_ids.append(hist_course_id)

    if not history_tags_list:
        print("Không tìm thấy tags hợp lệ cho các khóa học trong lịch sử. Không thể tạo hồ sơ người dùng.")
        return []

    history_profile_tags = " ".join(history_tags_list)
    if not history_profile_tags.strip():
        print("Hồ sơ lịch sử người dùng trống sau khi xử lý.")
        return []

    try:
        history_vector = vectorizer_g.transform([history_profile_tags])
    except Exception as e:
        print(f"Lỗi khi chuyển đổi hồ sơ lịch sử: {e}")
        return []

    profile_similarity_scores = cosine_similarity(history_vector, all_course_vectors_g)[0]
    course_list_indices_scores = sorted(list(enumerate(profile_similarity_scores)), reverse=True, key=lambda x: x[1])

    recommended_courses_details_list = []
    count = 0
    for i, score in course_list_indices_scores:
        course_id_at_index = feature_df_global.iloc[i]['course_id']
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
    global all_courses_df_processed, similarity_matrix_global, cv_vectorizer_global, all_course_vectors_global, is_data_loaded
    if is_data_loaded:
        print("Dữ liệu đã được tải trước đó.")
        return
    print("Đang tải dữ liệu ban đầu cho ứng dụng Flask...")
    raw_df = fetch_courses_from_oracle()
    if raw_df is not None and not raw_df.empty:
        all_courses_df_processed = preprocess_data_from_db(raw_df)
        if all_courses_df_processed is not None and not all_courses_df_processed.empty:
            similarity_matrix_global, cv_vectorizer_global, all_course_vectors_global = calculate_similarity_matrix_global(all_courses_df_processed)
            if similarity_matrix_global is not None:
                is_data_loaded = True
                print("Tải và tiền xử lý dữ liệu ban đầu thành công!")
            else: print("Lỗi: Không thể tính toán ma trận tương đồng.")
        else: print("Lỗi: Tiền xử lý dữ liệu thất bại.")
    else: print("Lỗi: Không thể tải dữ liệu từ Oracle DB.")

with app.app_context():
    load_initial_data()

@app.route('/recommend/<string:user_id>', methods=['GET'])
def recommend_for_user(user_id):
    global all_courses_df_processed, similarity_matrix_global, cv_vectorizer_global, all_course_vectors_global, is_data_loaded

    if not is_data_loaded:
        print("Dữ liệu chưa được tải, đang thử tải lại...")
        load_initial_data()
        if not is_data_loaded:
            return jsonify({"success": False, "message": "Lỗi máy chủ: Dữ liệu gợi ý chưa sẵn sàng.", "data": None}), 500

    if not user_id:
        return jsonify({"success": False, "message": "UserID không được cung cấp.", "data": None}), 400

    try:
        purchase_history_ids = fetch_purchase_history(user_id)
        if not purchase_history_ids:
            print(f"Không có lịch sử mua hàng cho userID {user_id} hoặc lấy lịch sử thất bại. Cung cấp gợi ý chung.")

            generic_recommendations = []
            if all_courses_df_processed is not None and not all_courses_df_processed.empty:
                temp_top_n = min(10, len(all_courses_df_processed))
                for i in range(temp_top_n):
                    course_id = all_courses_df_processed.iloc[i]['course_id']
                    course_details = fetch_course_details_php(course_id)
                    if course_details:
                        course_details["recommendation_score"] = 0.0
                        generic_recommendations.append(course_details)

            if generic_recommendations:
                 return jsonify({"success": True, "message": "Không có lịch sử mua hàng, đây là một số khóa học phổ biến.", "data": generic_recommendations}), 200
            else:
                 return jsonify({"success": True, "message": "Không có lịch sử mua hàng và không thể tạo gợi ý chung.", "data": []}), 200


        recommendations = get_recommendations_from_history_ids(
            purchase_history_ids,
            all_courses_df_processed,
            all_course_vectors_global,
            cv_vectorizer_global,
            similarity_matrix_global,
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
    global all_courses_df_processed, similarity_matrix_global, is_data_loaded

    if not is_data_loaded:
        print("Dữ liệu chưa được tải, đang thử tải lại...")
        load_initial_data()
        if not is_data_loaded:
            return jsonify({"success": False, "message": "Lỗi máy chủ: Dữ liệu gợi ý chưa sẵn sàng.", "data": None}), 500

    if not course_id_param:
        return jsonify({"success": False, "message": "CourseID không được cung cấp.", "data": None}), 400

    if all_courses_df_processed is None or similarity_matrix_global is None:
        return jsonify({"success": False, "message": "Lỗi máy chủ: Dữ liệu gợi ý chưa được tính toán.", "data": None}), 500

    if 'course_id' not in all_courses_df_processed.columns:
         return jsonify({"success": False, "message": "Lỗi cấu hình: Thiếu cột 'course_id'.", "data": None}), 500

    course_indices = all_courses_df_processed[all_courses_df_processed['course_id'] == course_id_param].index
    if course_indices.empty:
        return jsonify({"success": False, "message": f"Khóa học với ID '{course_id_param}' không có trong dữ liệu để tạo gợi ý.", "data": None}), 404

    course_index = course_indices[0]
    distances = similarity_matrix_global[course_index]
    course_list_indices_scores = sorted(list(enumerate(distances)), reverse=True, key=lambda x: x[1])

    recommended_courses_details_list = []
    count = 0
    top_n = 5
    for i, score in course_list_indices_scores:
        if all_courses_df_processed.iloc[i]['course_id'] == course_id_param:
            continue
        if count < top_n:
            rec_course_id = all_courses_df_processed.iloc[i]['course_id']
            course_details = fetch_course_details_php(rec_course_id)
            if course_details:
                course_details["recommendation_score"] = float(score)
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
