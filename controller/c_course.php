<?php
require_once __DIR__ . '/../service/service_course.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "Phương thức không được hỗ trợ.";
    exit;
}

// Lấy dữ liệu từ form
$courseID    = $_POST['CourseID'] ?? null;
$title       = $_POST['Title'] ?? '';
$price       = $_POST['Price'] ?? 0;
$instructor  = $_POST['Instructor'] ?? '';
$description = $_POST['Description'] ?? '';
$categories  = $_POST['Categories'] ?? [];

if (empty($title) || empty($price) || empty($instructor) || empty($categories)) {
    echo "Thiếu thông tin bắt buộc.";
    exit;
}

$service = new CourseService();

// Nếu là thêm mới -> tạo course trước, lấy ra courseID
if (!$courseID) {
    $createResult = $service->create_course($title, $description, $price, $instructor, $categories);
    if (!$createResult->success) {
        echo "Tạo khóa học thất bại: " . $createResult->message;
        exit;
    }
    $courseID = $createResult->data; // Lấy ID từ service
} else {
    $updateResult = $service->update_course($courseID, $title, $description, $price, $instructor, $categories);
    if (!$updateResult->success) {
        echo "Cập nhật khóa học thất bại: " . $updateResult->message;
        exit;
    }
}

// ✅ Xử lý upload ảnh
if (isset($_FILES['CourseImage']) && $_FILES['CourseImage']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = '../uploads/courses/';
    $fileTmp   = $_FILES['CourseImage']['tmp_name'];
    $fileName  = $_FILES['CourseImage']['name'];
    $ext       = pathinfo($fileName, PATHINFO_EXTENSION);
    $allowed   = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    if (!in_array(strtolower($ext), $allowed)) {
        echo "Định dạng ảnh không hợp lệ.";
        exit;
    }

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $newFileName = uniqid('course_', true) . '.' . $ext;
    $targetPath = $uploadDir . $newFileName;

    if (move_uploaded_file($fileTmp, $targetPath)) {
        // Lưu vào DB (gọi service lưu ảnh)
        $relativePath = 'uploads/courses/' . $newFileName;
        $service->save_course_image($courseID, $relativePath);
    }
}

// ✅ Điều hướng sau khi xử lý xong
header("Location: ../course_management.php?success=1");
exit;
