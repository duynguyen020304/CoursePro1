<?php
// api/course_instructor_api.php

require_once __DIR__ . '/../service/service_response.php';
require_once __DIR__ . '/../service/service_course_instructor.php';

header('Content-Type: application/json; charset=utf-8');

$service = new CourseInstructorService();
$method  = $_SERVER['REQUEST_METHOD'];

// lấy params từ query string
parse_str($_SERVER['QUERY_STRING'], $query);

// đọc body JSON nếu có
$body = json_decode(file_get_contents('php://input'), true);

switch ($method) {
    case 'GET':
        if (isset($query['courseID'])) {
            $res = $service->getByCourse($query['courseID']);
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
        $res = $service->delete(
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
