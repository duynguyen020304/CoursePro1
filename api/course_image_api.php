<?php

require_once __DIR__ . '/../service/service_course_image.php';
header('Content-Type: application/json');

$service = new CourseImageService();

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        // GET /api/course_image_api.php?courseID=...
        if (!isset($_GET['courseID'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu courseID']);
            exit;
        }
        $resp = $service->get_images($_GET['courseID']);
        http_response_code($resp->success ? 200 : 500);
        echo json_encode(['success' => $resp->success, 'message' => $resp->message, 'data' => $resp->data]);
        break;

    case 'POST':
        // Nhận file upload qua multipart/form-data
        if (!isset($_POST['courseID']) || !isset($_FILES['CourseImage'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu courseID hoặc file ảnh']);
            exit;
        }
        $courseID  = $_POST['courseID'];
        $caption   = $_POST['caption'] ?? null;
        $sortOrder = isset($_POST['sortOrder']) ? intval($_POST['sortOrder']) : 0;
        $file      = $_FILES['CourseImage'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Lỗi khi upload ảnh: ' . $file['error']]);
            exit;
        }

        // Kiểm tra định dạng
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($ext, $allowed)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Định dạng ảnh không hợp lệ']);
            exit;
        }

        // Lưu file
        $uploadDir = __DIR__ . '/../uploads/course_images/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        $newName = uniqid('img_', true) . '.' . $ext;
        $dest = $uploadDir . $newName;
        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Lưu file thất bại']);
            exit;
        }

        $imagePath = 'uploads/course_images/' . $newName;
        $resp = $service->add_image($courseID, $imagePath, $caption, $sortOrder);
        http_response_code($resp->success ? 201 : 500);
        echo json_encode(['success' => $resp->success, 'message' => $resp->message, 'data' => $resp->data]);
        break;

    case 'DELETE':
        // DELETE body JSON: { imageID }
        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['imageID'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu imageID']);
            exit;
        }
        $resp = $service->delete_image($data['imageID']);
        http_response_code($resp->success ? 200 : 404);
        echo json_encode(['success' => $resp->success, 'message' => $resp->message]);
        break;

    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Phương thức không được hỗ trợ']);
        break;
}
