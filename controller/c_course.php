<?php
require_once __DIR__ . '/../service/service_course.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "Phương thức không được hỗ trợ.";
    exit;
}

// Lấy dữ liệu từ form
$courseID    = $_POST['CourseID'] ?? null; // Có thể null nếu thêm mới
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

if ($courseID) {
    // Cập nhật khóa học
    $result = $service->update_course($courseID, $title, $description, $price, $instructor, $categories);
} else {
    // Tạo mới khóa học
    $result = $service->create_course($title, $description, $price, $instructor, $categories);
}

// Điều hướng về giao diện quản lý khóa học
header("Location: ../course_management.php?success=" . ($result->success ? 1 : 0));
exit;
