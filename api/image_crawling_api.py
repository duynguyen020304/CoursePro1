from enum import auto
import os
import io
import tempfile
import sqlite3
from PIL import Image
import imagehash
from icrawler.builtin import GoogleImageCrawler, BingImageCrawler
import logging
from flask import Flask, request, send_file, jsonify
from flask_cors import CORS
# from waitress import serve #enable this for windows compatibility
from concurrent.futures import ThreadPoolExecutor
import concurrent.futures

DB_FILE = "images.db"

def init_db():
    conn = sqlite3.connect(DB_FILE)
    cursor = conn.cursor()
    cursor.execute('''
        CREATE TABLE IF NOT EXISTS images (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            keyword TEXT NOT NULL,
            phash TEXT NOT NULL,
            timestamp DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ''')
    cursor.execute('CREATE INDEX IF NOT EXISTS idx_phash ON images (phash)')
    conn.commit()
    conn.close()
    print(f"Cơ sở dữ liệu '{DB_FILE}' đã được khởi tạo.")

def add_image_to_db(keyword, image_hash):
    conn = sqlite3.connect(DB_FILE)
    cursor = conn.cursor()
    cursor.execute("INSERT INTO images (keyword, phash) VALUES (?, ?)", (keyword, str(image_hash)))
    conn.commit()
    conn.close()
    print(f"Đã lưu hash '{image_hash}' cho từ khóa '{keyword}' vào CSDL.")

def is_image_duplicate(new_hash, threshold=5):
    conn = sqlite3.connect(DB_FILE)
    cursor = conn.cursor()
    cursor.execute("SELECT phash FROM images")
    existing_hashes = cursor.fetchall()
    conn.close()
    for row in existing_hashes:
        existing_hash = imagehash.hex_to_hash(row[0])
        if new_hash - existing_hash <= threshold:
            print(f"Phát hiện ảnh trùng lặp. Hash: {new_hash}, Hash cũ: {existing_hash}, Khoảng cách: {new_hash - existing_hash}")
            return True
    return False

def calculate_phash(image_data):
    try:
        img = Image.open(io.BytesIO(image_data))
        return imagehash.phash(img)
    except Exception as e:
        print(f"Lỗi khi tính pHash: {e}")
        return None

def convert_to_webp_in_memory(input_path, quality=70):
    if not os.path.exists(input_path):
        print(f"Lỗi: File đầu vào không tồn tại tại '{input_path}'")
        return None, None
    try:
        with Image.open(input_path) as img:
            if img.mode == 'P' or img.mode == 'PA':
                img = img.convert('RGBA')
            elif img.mode != 'RGB' and img.mode != 'RGBA':
                img = img.convert('RGB')
            img_byte_arr_png = io.BytesIO()
            img.save(img_byte_arr_png, "PNG")
            png_data = img_byte_arr_png.getvalue()
            img_byte_arr_webp = io.BytesIO()
            img.save(img_byte_arr_webp, "webp", quality=quality, optimize=True)
            webp_data = img_byte_arr_webp.getvalue()
            print(f"Đã chuyển đổi '{os.path.basename(input_path)}' sang PNG và WebP trong bộ nhớ.")
            return png_data, webp_data
    except Exception as e:
        print(f"Lỗi trong quá trình chuyển đổi '{input_path}': {e}")
        return None, None

def crawl_and_convert_image(title, language='vi'):
    with tempfile.TemporaryDirectory() as temp_dir:
        google_crawler = GoogleImageCrawler(
            parser_threads=2,
            downloader_threads=4,
            storage={"root_dir": temp_dir},
        )
        bing_crawler = BingImageCrawler(
            downloader_threads=4,
            storage={'root_dir': temp_dir}
        )
        print(f"Bắt đầu crawl ảnh cho từ khóa '{title}'...")
        try:
            google_crawler.crawl(keyword=title, max_num=10, file_idx_offset='auto', language=language)
            print("Crawl từ Google hoàn tất.")
        except Exception as e:
            print(f"Lỗi khi crawl từ Google: {e}")
        try:
            bing_crawler.crawl(keyword=title, max_num=10, offset=0, file_idx_offset='auto')
            print("Crawl từ Bing hoàn tất.")
        except Exception as e:
            print(f"Lỗi khi crawl từ Bing: {e}")
        crawled_files = [os.path.join(temp_dir, f) for f in os.listdir(temp_dir) if os.path.isfile(os.path.join(temp_dir, f))]
        if not crawled_files:
            print("Cả Google và Bing đều không tải về được file nào.")
            return None
        print(f"Tổng cộng có {len(crawled_files)} ảnh được tải về từ cả hai nguồn.")
        first_image_webp = None
        for i, temp_image_path in enumerate(crawled_files):
            print(f"Đang xử lý ảnh {i+1}/{len(crawled_files)}: {temp_image_path}")
            png_data, webp_data = convert_to_webp_in_memory(temp_image_path)
            if not png_data or not webp_data:
                continue
            if i == 0:
                first_image_webp = webp_data
            image_hash = calculate_phash(png_data)
            if image_hash and not is_image_duplicate(image_hash):
                add_image_to_db(title, image_hash)
                print(f"Tìm thấy ảnh không trùng lặp. Gửi ảnh: {os.path.basename(temp_image_path)}")
                return webp_data
        print("Tất cả ảnh tìm thấy đều trùng. Gửi ảnh đầu tiên làm phương án dự phòng.")
        return first_image_webp
init_db()
app = Flask(__name__)
CORS(app)

executor = ThreadPoolExecutor(max_workers=10)

logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

@app.route('/get-course-image', methods=['GET'])
def get_course_image():
    title = request.args.get('title')
    language = request.args.get('language', 'vi')
    if not title:
        return jsonify({"error": "Vui lòng cung cấp 'title'."}), 400

    logger.info("Nhận yêu cầu cho title='%s'. Đưa vào hàng đợi xử lý...", title)
    try:
        future = executor.submit(crawl_and_convert_image, title, language)
        image_data = future.result(timeout=600)
        if image_data:
            logger.info("Gửi ảnh thành công cho title: '%s'", title)
            return send_file(
                io.BytesIO(image_data),
                mimetype='image/webp',
                as_attachment=False
            )
        else:
            logger.info("Không thể tìm thấy hoặc xử lý ảnh phù hợp cho title='%s'.", title)
            return jsonify({"error": "Không thể tìm thấy hoặc xử lý ảnh phù hợp."}), 404

    except concurrent.futures.TimeoutError as te:
        logger.exception("Timeout khi xử lý title='%s': %s", title, te)
        return jsonify({"error": "Yêu cầu xử lý quá thời gian cho phép."}), 504

    except Exception as e:
        logger.exception("Lỗi không mong muốn khi xử lý title='%s'", title)
        return jsonify({"error": "Lỗi máy chủ nội bộ"}), 500

if __name__ == '__main__':
    init_db()
    # print("Khởi động máy chủ crawl images đa luồng")
    # serve(app, host='0.0.0.0', port=5000, threads=16) #enable this for windows compatibility