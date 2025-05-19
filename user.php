<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// --- Logic để xác định $app_root_path_relative và API_BASE ---
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http');
$host = $_SERVER['HTTP_HOST'];
$script_path = $_SERVER['SCRIPT_NAME'];
$path_parts = explode('/', ltrim($script_path, '/'));
$app_root_path_relative = '';

if (count($path_parts) > 0 && $path_parts[0] !== basename($script_path)) {
    $app_root_path_relative = '/' . $path_parts[0];
}
$known_app_subdir_markers = ['/admin/', '/api/', '/includes/'];
$found_marker = false;
foreach ($known_app_subdir_markers as $marker) {
    $pos = strpos($script_path, $marker);
    if ($pos !== false) {
        $app_root_path_relative = substr($script_path, 0, $pos);
        $found_marker = true;
        break;
    }
}
if (!$found_marker) {
    $app_root_path_relative = dirname($script_path);
    if (($app_root_path_relative === '/' || $app_root_path_relative === '\\') && $script_path !== '/') {
        $app_root_path_relative = '';
    } elseif ($app_root_path_relative === '.' && ltrim($script_path, '/') !== basename($script_path) ) {
        $app_root_path_relative = '';
    } elseif ($app_root_path_relative === '.') {
        $app_root_path_relative = '';
    }
}
if ($app_root_path_relative !== '/' && $app_root_path_relative !== '' && substr($app_root_path_relative, -1) === '/') {
    $app_root_path_relative = rtrim($app_root_path_relative, '/');
}
if ($app_root_path_relative === '/' && $script_path === '/') {
    $app_root_path_relative = '';
}

// Sử dụng hằng số đã định nghĩa trong header nếu có, nếu không thì định nghĩa lại
if (!defined('APP_ROOT_PATH_RELATIVE_HEADER')) {
    define('APP_ROOT_PATH_RELATIVE_USER', $app_root_path_relative);
} else {
    define('APP_ROOT_PATH_RELATIVE_USER', APP_ROOT_PATH_RELATIVE_HEADER);
}
if (!defined('API_BASE_HEADER')) {
    define('API_BASE_USER', $protocol . '://' . $host . APP_ROOT_PATH_RELATIVE_USER . '/api');
} else {
    define('API_BASE_USER', API_BASE_HEADER);
}
// Đường dẫn đến controller tải file (tương tự như trong cart.php và course-detail.php)
$file_loader_base_url = APP_ROOT_PATH_RELATIVE_USER . '/controller/c_file_loader.php';
// --- Kết thúc logic xác định đường dẫn ---


// --- Hàm callApi ---
// Đảm bảo hàm này được định nghĩa (có thể đã được include từ header.php hoặc định nghĩa lại ở đây nếu cần)
if (!function_exists('callApi')) {
    function callApi(string $endpoint, string $method = 'GET', array $payload = []): array {
        $url = API_BASE_USER . '/' . ltrim($endpoint, '/'); // Sử dụng API_BASE_USER
        $methodUpper = strtoupper($method);

        if ($methodUpper === 'GET' && !empty($payload)) {
            $url .= '?' . http_build_query($payload);
        }

        $headers_arr = ["Content-Type: application/json; charset=utf-8", "Accept: application/json"];
        $token = $_SESSION['user']['token'] ?? null;
        if ($token) {
            $headers_arr[] = "Authorization: Bearer " . $token;
        }
        $headers_str = implode("\r\n", $headers_arr);

        $options = [
            'http' => [
                'method'        => $methodUpper,
                'header'        => $headers_str,
                'ignore_errors' => true,
                'timeout'       => 15
            ]
        ];

        if ($methodUpper !== 'GET' && !empty($payload)) {
            $options['http']['content'] = json_encode($payload);
        } elseif (in_array($methodUpper, ['POST', 'PUT']) && empty($payload)) {
            $options['http']['content'] = '{}';
        }

        $context  = stream_context_create($options);
        $response = @file_get_contents($url, false, $context);

        $status_code = 0;
        if (isset($http_response_header[0]) && preg_match('{HTTP\/\S*\s(\d{3})}', $http_response_header[0], $match)) {
            $status_code = intval($match[1]);
        }

        if ($response === false) {
            return ['success' => false, 'message' => 'API connection failed. URL: ' . htmlspecialchars($url), 'http_status_code' => $status_code, 'data' => null];
        }

        $result = json_decode($response, true);

        if ($result === null && json_last_error() !== JSON_ERROR_NONE && !empty($response)) {
            return ['success' => false, 'message' => 'Invalid API response (not JSON). Error: ' . json_last_error_msg(), 'http_status_code' => $status_code, 'data' => null, 'raw_response' => $response];
        }

        if (!is_array($result)) $result = [];
        $result['http_status_code'] = $status_code;
        if (!isset($result['success'])) {
            $result['success'] = ($status_code >= 200 && $status_code < 300);
        }
        return $result;
    }
}
// --- Kết thúc hàm callApi ---


// --- Lấy thông tin người dùng hiện tại ---
$user_profile_data = null; // Đổi tên biến để tránh xung đột
$user_fetch_error = null;
$loggedInUserID = $_SESSION['user']['userID'] ?? null;

if ($loggedInUserID) {
    $userResp = callApi('user_api.php', 'GET', ['id' => $loggedInUserID]);
    if (isset($userResp['success']) && $userResp['success'] && isset($userResp['data'])) {
        $user_profile_data = $userResp['data'];
    } else {
        $user_fetch_error = $userResp['message'] ?? 'Không thể tải thông tin người dùng.';
    }
} else {
    $user_fetch_error = 'Người dùng chưa đăng nhập hoặc userID không hợp lệ.';
    // Cân nhắc chuyển hướng đến trang đăng nhập nếu chưa đăng nhập
    // header('Location: signin.php');
    // exit;
}
// --- Kết thúc lấy thông tin người dùng ---


// --- Lấy danh sách khóa học đã mua ---
$purchased_courses_details = [];
$purchased_courses_error = null;

if ($loggedInUserID && !$user_fetch_error) { // Chỉ lấy khóa học nếu đã đăng nhập và có thông tin user
    // 1. Get orders by userID
    $orders_response = callApi('order_api.php', 'GET', ['userID' => $loggedInUserID]);

    if (isset($orders_response['success']) && $orders_response['success'] && !empty($orders_response['data'])) {
        $orders = $orders_response['data'];
        foreach ($orders as $order) {
            if (isset($order['orderID'])) {
                // 2. Get order details for each order
                $order_details_response = callApi('order_detail_api.php', 'GET', ['orderID' => $order['orderID']]);
                if (isset($order_details_response['success']) && $order_details_response['success'] && !empty($order_details_response['data'])) {
                    $details = $order_details_response['data'];
                    foreach ($details as $detail) {
                        if (isset($detail['courseID']) && !isset($purchased_courses_details[$detail['courseID']])) { // Tránh trùng lặp nếu 1 khóa học ở nhiều order
                            // 3. Get course information
                            $course_resp = callApi('course_api.php', 'GET', ['courseID' => $detail['courseID']]);
                            if (isset($course_resp['success']) && $course_resp['success'] && isset($course_resp['data'])) {
                                $course_info = $course_resp['data'];
                                if (isset($course_info['courseID'])) { // Đảm bảo là object khóa học đơn lẻ
                                    $purchased_courses_details[$course_info['courseID']] = [
                                        'title' => $course_info['title'] ?? 'N/A',
                                        'imagePath' => $course_info['images'][0]['imagePath'] ?? null,
                                        'courseID' => $course_info['courseID'],
                                        'description_short' => isset($course_info['description']) ? mb_substr($course_info['description'], 0, 70) . '...' : 'Mô tả ngắn...'
                                        // Thêm các thông tin khác nếu cần, ví dụ: link đến trang học
                                    ];
                                }
                            } else {
                                error_log("UserPage: Failed to fetch course details for courseID " . $detail['courseID'] . ". Message: " . ($course_resp['message'] ?? 'Unknown error'));
                            }
                        }
                    }
                } else {
                    error_log("UserPage: Failed to fetch order details for orderID " . $order['orderID'] . ". Message: " . ($order_details_response['message'] ?? 'Unknown error'));
                }
            }
        }
        if (empty($purchased_courses_details) && empty($orders_response['data'])) {
            // $purchased_courses_error = "Bạn chưa có đơn hàng nào."; // Nếu không có đơn hàng nào
        } elseif (empty($purchased_courses_details) && !empty($orders_response['data'])) {
            // Có đơn hàng nhưng không lấy được chi tiết khóa học, hoặc chi tiết trống
            $purchased_courses_error = "Không tìm thấy khóa học nào trong các đơn hàng của bạn hoặc có lỗi khi tải chi tiết khóa học.";
        }

    } elseif (isset($orders_response['success']) && $orders_response['success'] && empty($orders_response['data'])) {
        // $purchased_courses_error = "Bạn chưa đăng ký khóa học nào."; // Không có đơn hàng nào
    }
    else {
        $purchased_courses_error = "Không thể tải danh sách khóa học đã mua. " . ($orders_response['message'] ?? '');
    }
} elseif (!$loggedInUserID) {
    $purchased_courses_error = "Vui lòng đăng nhập để xem các khóa học đã đăng ký.";
}
// --- Kết thúc lấy danh sách khóa học đã mua ---

$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Học viên - <?php echo htmlspecialchars($user_profile_data['firstName'] ?? 'Người dùng'); ?></title>
    <link href="<?php echo APP_ROOT_PATH_RELATIVE_USER; ?>/public/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <link href="<?php echo APP_ROOT_PATH_RELATIVE_USER; ?>/public/css/user.css" rel="stylesheet" />
</head>
<body>

<?php include('template/user_sidebar.php'); ?>

<div class="main-content">
    <div class="topbar-sm d-lg-none d-flex justify-content-between align-items-center mb-3">
        <button class="btn btn-dark" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarOffcanvas" aria-controls="sidebarOffcanvas">
            <i class="bi bi-list"></i>
        </button>
        <h5 class="mb-0">Dashboard</h5>
        <div></div>
    </div>

    <div class="container-fluid">
        <?php if ($user_fetch_error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($user_fetch_error); ?></div>
        <?php endif; ?>

        <div class="row">
            <div class="col-12">
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <i class="bi bi-bell-fill me-2"></i> Bạn có 2 thông báo mới!
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0"><i class="bi bi-journal-bookmark-fill me-2"></i> Các khóa học đã đăng ký</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($purchased_courses_error): ?>
                            <div class="alert alert-secondary"><?php echo htmlspecialchars($purchased_courses_error); ?></div>
                        <?php elseif (empty($purchased_courses_details)): ?>
                            <p class="text-center text-muted">Bạn chưa đăng ký khóa học nào.</p>
                            <div class="text-center">
                                <a href="<?php echo APP_ROOT_PATH_RELATIVE_USER; ?>/home.php" class="btn btn-primary">Khám phá khóa học</a>
                            </div>
                        <?php else: ?>
                            <?php foreach ($purchased_courses_details as $course): ?>
                                <div class="d-flex align-items-center mb-4 pb-2 border-bottom">
                                    <?php
                                    $course_image_url = APP_ROOT_PATH_RELATIVE_USER . "/public/images/course_placeholder.png"; // Default
                                    if (!empty($course['imagePath']) && !empty($course['courseID'])) {
                                        $image_filename = basename($course['imagePath']);
                                        $course_image_url = htmlspecialchars($file_loader_base_url . "?act=serve_image&course_id=" . urlencode($course['courseID']) . "&image=" . urlencode($image_filename));
                                    }
                                    ?>
                                    <img src="<?php echo $course_image_url; ?>" alt="<?php echo htmlspecialchars($course['title']); ?>" class="course-thumbnail" onerror="this.onerror=null;this.src='<?php echo APP_ROOT_PATH_RELATIVE_USER; ?>/public/images/course_placeholder.png';" />
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 course-title-link"><a href="<?php echo APP_ROOT_PATH_RELATIVE_USER . '/course-detail.php?courseID=' . htmlspecialchars($course['courseID']); ?>"><?php echo htmlspecialchars($course['title']); ?></a></h6>
                                        <div class="progress" style="height: 10px;" aria-label="Tiến độ khóa học">
                                            <div class="progress-bar bg-success" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                        <small class="text-muted">Đã hoàn thành 0% (placeholder)</small>
                                    </div>
                                    <a href="<?php echo APP_ROOT_PATH_RELATIVE_USER . '/learning.php?courseID=' . htmlspecialchars($course['courseID']); ?>" class="btn btn-sm btn-primary ms-3">Bắt đầu học</a>
                                </div>
                            <?php endforeach; ?>
                            <?php if (count($purchased_courses_details) > 3) : // Giả sử chỉ hiển thị 3 khóa đầu, còn lại có nút "Xem tất cả" ?>
                                <div class="text-center mt-3">
                                    <a href="#my-courses" class="btn btn-outline-secondary btn-sm">Xem tất cả khóa học đã đăng ký</a>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <?php if ($user_profile_data): ?>
                    <div class="card shadow-sm mb-4">
                        <div class="card-body text-center">
                            <?php
                            $profileImagePath = APP_ROOT_PATH_RELATIVE_USER . "/public/images/default_avatar.png"; // Default
                            if (!empty($user_profile_data['profileImage'])) {
                                // Giả sử profileImage là tên file và userID được dùng làm thư mục
                                $profileImagePath = htmlspecialchars($file_loader_base_url . '?act=serve_user_image&user_id=' . urlencode($loggedInUserID) . '&image=' . urlencode($user_profile_data['profileImage']));
                            } else {
                                // Nếu không có profileImage, thử tạo avatar từ tên
                                $firstNameInitial = !empty($user_profile_data['firstName']) ? mb_substr(trim($user_profile_data['firstName']), 0, 1, 'UTF-8') : '';
                                $lastNameInitial  = !empty($user_profile_data['lastName']) ? mb_substr(trim($user_profile_data['lastName']), 0, 1, 'UTF-8') : '';
                                $avatarText = ($firstNameInitial && $lastNameInitial) ? strtoupper($firstNameInitial . $lastNameInitial) : '??';
                                // Bạn có thể tạo một ảnh placeholder với chữ cái đầu bằng cách dùng một service hoặc thư viện,
                                // hoặc đơn giản là không hiển thị ảnh nếu không có.
                                // Hiện tại, nếu không có ảnh, sẽ dùng default_avatar.png
                            }
                            ?>
                            <img src="<?= $profileImagePath ?>" alt="Avatar" class="profile-avatar mb-2" onerror="this.onerror=null;this.src='<?php echo APP_ROOT_PATH_RELATIVE_USER; ?>/public/images/default_avatar.png';" />

                            <h5 class="card-title">
                                Chào <?php echo htmlspecialchars(($user_profile_data['firstName'] ?? '') . " " . ($user_profile_data['lastName'] ?? 'Người dùng')); ?>!
                            </h5>
                            <p class="card-text text-muted mb-1">
                                Email: <?php echo htmlspecialchars($user_profile_data['email'] ?? 'N/A'); ?>
                            </p>
                            <?php if (isset($user_profile_data['created_at']) && !empty($user_profile_data['created_at'])): ?>
                                <p class="card-text text-muted mb-3">
                                    Ngày tham gia:
                                    <?php
                                    try {
                                        $date = new DateTime($user_profile_data['created_at']);
                                        echo $date->format('d/m/Y');
                                    } catch (Exception $e) {
                                        echo 'N/A';
                                    }
                                    ?>
                                </p>
                            <?php endif; ?>
                            <a href="<?php echo APP_ROOT_PATH_RELATIVE_USER; ?>/edit-profile.php" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-pencil-square me-1"></i> Chỉnh sửa Hồ sơ
                            </a>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0"><i class="bi bi-lightbulb-fill me-2"></i> Gợi ý cho bạn</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <img src="public/img/reactjs_course.png" alt="Suggested Course" class="course-thumbnail" onerror="this.onerror=null;this.src='https://placehold.co/80x50/dee2e6/6c757d.png?text=Suggest+1';"/>
                            <div class="flex-grow-1">
                                <h6 class="mb-0 fs-sm">Khóa Học ReactJS Nâng Cao</h6>
                                <small class="text-muted">Lập trình viên</small>
                            </div>
                            <a href="#" class="btn btn-sm btn-outline-success ms-2" title="Xem chi tiết"><i class="bi bi-arrow-right-circle-fill"></i></a>
                        </div>
                        <div class="d-flex align-items-center">
                            <img src="<?php echo APP_ROOT_PATH_RELATIVE_USER; ?>/public/img/photograph_course.webp" alt="Suggested Course" class="course-thumbnail" onerror="this.onerror=null;this.src='https://placehold.co/80x50/dee2e6/6c757d.png?text=Suggest+2';"/>
                            <div class="flex-grow-1">
                                <h6 class="mb-0 fs-sm">Nghệ Thuật Nhiếp Ảnh Cơ Bản</h6>
                                <small class="text-muted">Nhiếp ảnh</small>
                            </div>
                            <a href="#" class="btn btn-sm btn-outline-success ms-2" title="Xem chi tiết"><i class="bi bi-arrow-right-circle-fill"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <footer class="text-center text-muted mt-4 py-3">
        <small>&copy; <?php echo date('Y'); ?> Course Online. All Rights Reserved.</small>
    </footer>
</div>
<script src="<?php echo APP_ROOT_PATH_RELATIVE_USER; ?>/public/js/bootstrap.bundle.min.js"></script>
<script>

</script>
</body>
</html>
