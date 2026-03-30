<?php
// Public API - no authentication required
require_once __DIR__ . '/../../Service/service_course.php';

header("Content-Type: application/json");

$service = new CourseService();

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        $isGetForCourseManagement = $_GET['isGetForCourseManagement'] ?? null;
        $difficulty = $_GET['difficulty'] ?? null;
        $language = $_GET['language'] ?? null;
        if ($isGetForCourseManagement !== null && filter_var($isGetForCourseManagement, FILTER_VALIDATE_BOOLEAN) && isset($_GET['title']) && !empty($_GET['title'])) {
            $title = $_GET['title'];
            $response = $service->search_courses_by_title_for_course_management($title);
            if ($response->success) {
                http_response_code(200);
                echo json_encode([
                    'success' => $response->success,
                    'data'    => $response->data
                ]);
                break;
            } else {
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'data'    => null
                ]);
                break;
            }
        }
        else if (isset($_GET['title']) && !empty($_GET['title'])) {
            $title = $_GET['title'];
            $response = $service->search_courses_by_title($title);
        } else {
            $response = $service->get_all_courses();
        }

        if ($response->success) {
            http_response_code(200);
        } else {
            http_response_code(500);
        }
        echo json_encode($response->data);
        break;

    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Phương thức không được hỗ trợ']);
        break;
}