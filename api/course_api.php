<?php

$secretKey = '0196ce3e-ba28-7b47-8472-beded9ae0b5d';

require_once __DIR__ . '/../service/service_course.php';
require __DIR__ . '/../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

header("Content-Type: application/json");

//if ($_SERVER['REQUEST_METHOD'] !== "GET") {
//    $authHeader = apache_request_headers();
//    $token = null;
//
//    if (isset($authHeader['Authorization'])) {
//        if (preg_match('/Bearer\s(\S+)/', $authHeader['Authorization'], $matches)) {
//            $token = $matches[1];
//        }
//    }
//
//    if (!$token) {
//        http_response_code(401);
//        echo json_encode(['success' => false, 'message' => 'Không tìm thấy token xác thực.']);
//        exit;
//    }
//
//    try {
//        $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));
//    } catch (Firebase\JWT\ExpiredException $e) {
//        http_response_code(401);
//        echo json_encode(['success' => false, 'message' => 'Token đã hết hạn.']);
//        exit;
//    } catch (Firebase\JWT\SignatureInvalidException $e) {
//        http_response_code(401);
//        echo json_encode(['success' => false, 'message' => 'Chữ ký token không hợp lệ.']);
//        exit;
//    } catch (Exception $e) {
//        http_response_code(401);
//        echo json_encode(['success' => false, 'message' => 'Token không hợp lệ hoặc có lỗi xảy ra: ' . $e->getMessage()]);
//        exit;
//    }
//}

$service = new CourseService();

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        $response = null;

        $pageParam = $_GET['page'] ?? null;
        $pageSizeParam = $_GET['pageSize'] ?? null;
        $difficultyParam = $_GET['difficulty'] ?? null;
        $languageParam = $_GET['language'] ?? null;

        $isGetAllCourseParam = $_GET['isGetAllCourse'] ?? null;
        $optionParam = $_GET['option'] ?? 1;
        $optionParam = filter_var($optionParam, FILTER_VALIDATE_INT);
        $courseIDParam = $_GET['courseID'] ?? null;
        $isFilterByCategory = $_GET['isFilterByCategory'] ?? null;
        $isGetCourseForRecommend = $_GET['isGetCourseForRecommend'] ?? null;

        if ($pageParam !== null) {
            $pageNumber = filter_var($pageParam, FILTER_VALIDATE_INT);
            $pageSize = 10;
            if ($pageSizeParam !== null) {
                $pageSizeValidated = filter_var($pageSizeParam, FILTER_VALIDATE_INT);
                if ($pageSizeValidated && $pageSizeValidated > 0) {
                    $pageSize = $pageSizeValidated;
                } else {
                    http_response_code(400);
                    echo json_encode([
                        'success' => false,
                        'message' => 'pageSize không hợp lệ. Phải là một số nguyên dương.',
                        'data'    => null
                    ]);
                    exit;
                }
            }

            if ($pageNumber && $pageNumber > 0) {
                $filterDifficulty = !empty($difficultyParam) ? trim($difficultyParam) : null;
                $filterLanguage = !empty($languageParam) ? trim($languageParam) : null;
                $response = $service->get_courses_paginated_service($pageNumber, $pageSize, $filterDifficulty, $filterLanguage);
            } else {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'page không hợp lệ. Phải là một số nguyên dương.',
                    'data'    => null
                ]);
                exit;
            }
        } elseif ($pageParam === null && (!empty($difficultyParam) || !empty($languageParam))) {
            $filterDifficulty = !empty($difficultyParam) ? trim($difficultyParam) : null;
            $filterLanguage = !empty($languageParam) ? trim($languageParam) : null;

            if ($filterDifficulty !== null || $filterLanguage !== null) {
                $response = $service->get_courses_by_difficulty_lang_service($filterDifficulty, $filterLanguage);
            } else {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Cần cung cấp ít nhất một tiêu chí lọc (difficulty hoặc language).',
                    'data'    => null
                ]);
                exit;
            }
        } elseif ($isGetAllCourseParam !== null && filter_var($isGetAllCourseParam, FILTER_VALIDATE_BOOLEAN)) {
            if ($optionParam === 0) {
                $response = $service->get_all_courses();
            } else if ($optionParam === 1) {
                $response = $service->get_k_courses_for_home_page(8);
            } else if ($optionParam === 2) {
                $response = $service->get_all_courses_for_course_management();
            } else if ($optionParam === 3) {
                $response = $service->get_all_courses_for_upload_video();
            }
        } elseif ($courseIDParam !== null && $isFilterByCategory !== null && filter_var($isFilterByCategory, FILTER_VALIDATE_BOOLEAN)) {
            if (empty($courseIDParam)) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'courseID parameter cannot be empty when provided for category filter.',
                    'data'    => null
                ]);
                exit;
            }
            $response = $service->get_course_by_id_for_category_filter($courseIDParam);
        } elseif ($courseIDParam !== null && $isGetCourseForRecommend !== null && filter_var($isGetCourseForRecommend, FILTER_VALIDATE_BOOLEAN)) {
            if (empty($courseIDParam)) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'courseID parameter cannot be empty when provided for recommend.',
                    'data'    => null
                ]);
                exit;
            }
            $response = $service->get_course_for_recommend($courseIDParam);
        } elseif ($courseIDParam !== null) {
            if (empty($courseIDParam)) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'courseID parameter cannot be empty when provided.',
                    'data'    => null
                ]);
                exit;
            }
            $response = $service->get_course_by_id($courseIDParam);
        }

        if ($response !== null) {
            http_response_code($response->success ? 200 : ($response->message === 'Không tìm thấy khóa học' || strpos($response->message, 'Không tìm thấy') !== false ? 404 : 500));
            echo json_encode([
                'success' => $response->success,
                'message' => $response->message,
                'data'    => $response->data
            ]);
        } else {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Invalid or missing GET parameters, and no specific route matched.',
                'data'    => null
            ]);
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);

        $requiredFields = ['title', 'price', 'difficulty', 'language'];
        $requiredArrayFields = ['instructorsID', 'categoriesID'];
        $missingFields = [];
        $invalidArrayFields = [];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                $missingFields[] = $field;
            }
        }

        foreach ($requiredArrayFields as $field) {
            if (!isset($data[$field])) {
                $missingFields[] = $field;
            } elseif (!is_array($data[$field]) || empty($data[$field])) {
                $invalidArrayFields[] = $field;
            }
        }

        if (!empty($missingFields)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Thiếu các dữ liệu đầu vào bắt buộc: ' . implode(', ', $missingFields)
            ]);
            exit;
        }

        if (!empty($invalidArrayFields)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Các trường sau phải là mảng không rỗng: ' . implode(', ', $invalidArrayFields)
            ]);
            exit;
        }

        $description = $data['description'] ?? null;
        $createdBy = $data['createdBy'] ?? ($decoded->userID ?? 'UNKNOWN');

        $response = $service->create_course(
            $data['title'],
            $description,
            floatval($data['price']),
            $data['instructorsID'],
            $data['categoriesID'],
            $data['difficulty'],
            $data['language'],
            $createdBy
        );

        http_response_code($response->success ? 201 : 500);
        echo json_encode([
            'success' => $response->success,
            'message' => $response->message,
            'course_id' => $response->data ?? null
        ]);
        break;

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"), true);

        $requiredFields = ['courseID', 'title', 'price', 'difficulty', 'language'];
        $requiredArrayFields = ['instructorsID', 'categoriesID'];
        $missingFields = [];
        $invalidArrayFields = [];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                $missingFields[] = $field;
            }
        }

        foreach ($requiredArrayFields as $field) {
            if (!isset($data[$field])) {
                $missingFields[] = $field;
            } elseif (!is_array($data[$field]) || empty($data[$field])) {
                $invalidArrayFields[] = $field;
            }
        }

        if (!empty($missingFields)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Thiếu các dữ liệu cần cập nhật bắt buộc: ' . implode(', ', $missingFields)
            ]);
            exit;
        }

        if (!empty($invalidArrayFields)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Các trường sau phải là mảng không rỗng khi cập nhật: ' . implode(', ', $invalidArrayFields)
            ]);
            exit;
        }

        $description = $data['description'] ?? null;

        $response = $service->update_course(
            $data['courseID'],
            $data['title'],
            $description,
            floatval($data['price']),
            $data['instructorsID'],
            $data['categoriesID'],
            $data['difficulty'],
            $data['language']
        );

        http_response_code($response->success ? 200 : ($response->message === 'Khóa học không tồn tại.' ? 404 : 500));
        echo json_encode(['success' => $response->success, 'message' => $response->message]);
        break;

    case 'DELETE':
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['courseID']) || empty(trim($data['courseID']))) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu dữ liệu cần thiết để xóa: courseID']);
            exit;
        }

        $response = $service->delete_course($data['courseID']);

        http_response_code($response->success ? 200 : ($response->message === 'Khóa học không tồn tại' ? 404 : 500));
        echo json_encode(['success' => $response->success, 'message' => $response->message]);
        break;

    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Phương thức không được hỗ trợ']);
        break;
}
?>