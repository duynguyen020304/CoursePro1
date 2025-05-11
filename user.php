<?php
// Bật session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Kiểm tra đã login chưa
if (!isset($_SESSION['user']['userID'])) {
    header('Location: /login.php');
    exit;
}

// Nạp class service và DTO
require_once __DIR__ . '/service/service_user.php';
require_once __DIR__ . '/model/dto/user_dto.php';

// Khởi tạo service và gọi lấy user
$userService = new UserService();
$response    = $userService->get_user_by_id($_SESSION['user']['userID']);

if (!$response->success) {
    // nếu có lỗi, bạn có thể redirect hoặc show message
    die('Không tìm thấy thông tin người dùng: ' . htmlspecialchars($response->message));
}

// Đưa đối tượng user ra biến $user cho dễ dùng
/** @var \App\DTO\UserDTO $user */
$user = $response->data;

// Xác định trang hiện tại để active menu
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Học viên - <?= htmlspecialchars($user->name) ?></title>
    <link href="public/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        /* TOÀN BỘ CSS GỐC CỦA BẠN SẼ NẰM Ở ĐÂY */
        body {
            background-color: #f8f9fa;
        }
        .sidebar {
            width: 260px;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            background-color: #343a40;
            padding-top: 1rem;
            transition: all 0.3s;
            z-index: 1030;
        }
        .sidebar .nav-link {
            color: #adb5bd;
            padding: 0.75rem 1.5rem;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: #fff;
            background-color: #495057;
        }
        .sidebar .sidebar-header {
            color: #fff;
            padding: 0 1.5rem 1rem 1.5rem;
            font-size: 1.2rem;
            font-weight: bold;
        }
        .main-content {
            transition: margin-left 0.3s;
            padding: 1.5rem;
            overflow-x: hidden;
        }
        @media (min-width: 992px) {
            .main-content {
                margin-left: 260px;
            }
            .navbar-toggler-icon {
                display: none !important;
            }
        }
        .offcanvas-start {
            width: 260px;
            background-color: #343a40;
        }
        .offcanvas-header {
            border-bottom: 1px solid #495057;
        }
        .offcanvas-title {
            color: #fff;
        }
        .offcanvas-body .nav-link {
            color: #adb5bd;
        }
        .offcanvas-body .nav-link:hover,
        .offcanvas-body .nav-link.active {
            color: #fff;
            background-color: #495057;
        }
        .btn-close-white {
            filter: invert(1) grayscale(100%) brightness(200%);
        }
        .topbar-sm {
            background-color: #fff;
            border-bottom: 1px solid #dee2e6;
            padding: 0.5rem 1rem;
            position: sticky;
            top: 0;
            z-index: 1020;
        }
        .card {
            margin-bottom: 1.5rem;
        }
        .profile-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 1rem;
            margin-bottom: 0.5rem;
        }
        .course-thumbnail {
            width: 80px;
            height: 50px;
            object-fit: cover;
            margin-right: 1rem;
            border-radius: 0.25rem;
        }
        .nav-link i {
            vertical-align: middle;
        }
    </style>
</head>

<body>

    <?php include('template/user_sidebar.php'); // Include template sidebar người dùng ?>

    <div class="main-content">
        <div class="topbar-sm d-lg-none d-flex justify-content-between align-items-center mb-3">
            <button class="btn btn-dark" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarOffcanvas" aria-controls="sidebarOffcanvas">
                <i class="bi bi-list"></i>
            </button>
            <h5 class="mb-0">Dashboard</h5> <div></div>
        </div>

        <div class="container-fluid">
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
                            <div class="d-flex align-items-center mb-4 pb-2 border-bottom">
                                <img src="https://via.placeholder.com/80x50/dee2e6/6c757d.png?text=Course+1" alt="Course Thumbnail" class="course-thumbnail">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">Tên Khóa Học Rất Dài Để Kiểm Tra Xuống Dòng</h6>
                                    <div class="progress" style="height: 10px;">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: 75%;" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <small class="text-muted">Đã hoàn thành 75%</small>
                                </div>
                                <a href="#" class="btn btn-sm btn-primary ms-3">Tiếp tục học</a>
                            </div>
                            <div class="d-flex align-items-center mb-4 pb-2 border-bottom">
                                <img src="https://via.placeholder.com/80x50/dee2e6/6c757d.png?text=Course+2" alt="Course Thumbnail" class="course-thumbnail">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">Khóa Học Lập Trình Web Cơ Bản</h6>
                                    <div class="progress" style="height: 10px;">
                                        <div class="progress-bar bg-info" role="progressbar" style="width: 30%;" aria-valuenow="30" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <small class="text-muted">Đã hoàn thành 30%</small>
                                </div>
                                <a href="#" class="btn btn-sm btn-primary ms-3">Tiếp tục học</a>
                            </div>
                            <div class="d-flex align-items-center"> <img src="https://via.placeholder.com/80x50/dee2e6/6c757d.png?text=Course+3" alt="Course Thumbnail" class="course-thumbnail">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">Thiết Kế Giao Diện UI/UX</h6>
                                    <div class="progress" style="height: 10px;">
                                        <div class="progress-bar bg-warning" role="progressbar" style="width: 10%;" aria-valuenow="10" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <small class="text-muted">Đã hoàn thành 10%</small>
                                </div>
                                <a href="#" class="btn btn-sm btn-primary ms-3">Tiếp tục học</a>
                            </div>
                            <div class="text-center mt-3">
                                <a href="#my-courses" class="btn btn-outline-secondary btn-sm">Xem tất cả khóa học</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card shadow-sm mb-4">
                        <div class="card-body text-center">
                            <img
                                src="<?= htmlspecialchars(
                                    !empty($user->profileImage)
                                        ? '/media/' . $user->profileImage
                                        : '/public/img/avatar-user.png'
                                )?>"
                                alt="Avatar"
                                class="profile-avatar mb-2"
                            >

                            <h5 class="card-title">
                                Chào, <?= htmlspecialchars($user->name) ?>!
                            </h5>

                            <p class="card-text text-muted mb-1">
                                Email: <?= htmlspecialchars($user->email) ?>
                            </p>

                            <?php if (isset($user->createdAt) && !empty($user->createdAt)): ?>
                            <p class="card-text text-muted mb-3">
                                Ngày tham gia:
                                <?= date('d/m/Y', strtotime($user->createdAt)) ?>
                            </p>
                            <?php endif; ?>

                            <a href="edit-profile.php" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-pencil-square me-1"></i> Chỉnh sửa Hồ sơ
                            </a>
                        </div>
                    </div>

                    <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="card-title mb-0"><i class="bi bi-lightbulb-fill me-2"></i> Gợi ý cho bạn</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <img src="https://via.placeholder.com/80x50/dee2e6/6c757d.png?text=Suggest+1" alt="Suggested Course" class="course-thumbnail">
                                <div class="flex-grow-1">
                                    <h6 class="mb-0 fs-sm">Khóa Học ReactJS Nâng Cao</h6>
                                    <small class="text-muted">Lập trình viên</small>
                                </div>
                                <a href="#" class="btn btn-sm btn-outline-success ms-2" title="Xem chi tiết"><i class="bi bi-arrow-right-circle-fill"></i></a>
                            </div>
                            <div class="d-flex align-items-center"> <img src="https://via.placeholder.com/80x50/dee2e6/6c757d.png?text=Suggest+2" alt="Suggested Course" class="course-thumbnail">
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
        <footer class="text-center text-muted mt-4">
            <small>&copy; <?= date('Y') ?> Tên Website. All Rights Reserved.</small>
        </footer>

    </div>
    <script src="public/js/bootstrap.bundle.min.js"></script>
</body>
</html>