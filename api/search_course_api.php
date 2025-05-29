<?php
$secretKey = '0196ce3e-ba28-7b47-8472-beded9ae0b5d';
require_once __DIR__ . '/../service/service_course.php';
header("Content-Type: application/json");

$service = new CourseService();

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        // Check if a 'title' parameter is provided for searching
        if (isset($_GET['title']) && !empty($_GET['title'])) {
            $title = $_GET['title'];
            $response = $service->search_courses_by_title($title);
        } else {
            $response = $service->get_all_courses();
        }

        if ($response->success) {
            http_response_code(200); // OK
        } else {
            http_response_code(500); // Internal Server Error or other appropriate code
        }
        echo json_encode($response->data);
        break;

    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Phương thức không được hỗ trợ']);
        break;
}
