<?php
require_once __DIR__ . '/../../Model/Config/auth_helper.php';
require_once __DIR__ . '/../../Service/service_response.php';
require_once __DIR__ . '/../../Service/service_course_instructor.php';

header('Content-Type: application/json; charset=utf-8');

// Require authentication for all requests
$userData = AuthHelper::requireAuth();

$service = new CourseInstructorService();
$method  = $_SERVER['REQUEST_METHOD'];

parse_str($_SERVER['QUERY_STRING'], $query);
$body = json_decode(file_get_contents('php://input'), true);

switch ($method) {
    case 'GET':
        if (isset($query['courseID'])) {
            $res = $service->get_instructors_by_course_id($query['courseID']);
        } else {
            $res = new ServiceResponse(false, 'Thiếu parameter: courseID');
        }
        echo json_encode($res);
        break;

    case 'POST':
        $res = $service->add(
            $body['courseID']     ?? '',
            $body['instructorID'] ?? ''
        );
        echo json_encode($res);
        break;

    case 'PUT':
        $res = $service->update(
            $body['oldCourseID']     ?? '',
            $body['oldInstructorID'] ?? '',
            $body['newCourseID']     ?? '',
            $body['newInstructorID'] ?? ''
        );
        echo json_encode($res);
        break;

    case 'DELETE':
        $res = $service->unlink_course_instructor(
            $body['courseID']     ?? '',
            $body['instructorID'] ?? ''
        );
        echo json_encode($res);
        break;

    default:
        http_response_code(405);
        echo json_encode(new ServiceResponse(false, 'Method Not Allowed'));
        break;
}