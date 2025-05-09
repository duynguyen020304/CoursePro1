<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Học viên</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        body {
            background-color: #f8f9fa;
            /* Light gray background */
        }

        .sidebar {
            width: 260px;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            background-color: #343a40;
            /* Dark background for sidebar */
            padding-top: 1rem;
            transition: all 0.3s;
            z-index: 1030;
            /* Ensure sidebar is above content */
        }

        .sidebar .nav-link {
            color: #adb5bd;
            /* Lighter text color */
            padding: 0.75rem 1.5rem;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: #fff;
            /* White text on hover/active */
            background-color: #495057;
            /* Slightly lighter dark background */
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
            /* Prevent horizontal scroll */
        }

        /* Adjust main content margin when sidebar is visible */
        @media (min-width: 992px) {
            .main-content {
                margin-left: 260px;
            }

            /* Hide the offcanvas toggle button on large screens */
            .navbar-toggler-icon {
                display: none !important;
            }
        }


        /* --- Offcanvas specific styles (for small screens) --- */
        .offcanvas-start {
            width: 260px;
            /* Match sidebar width */
            background-color: #343a40;
            /* Dark background */
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
            /* Make close button visible on dark bg */
        }


        /* --- Top bar for small screens --- */
        .topbar-sm {
            background-color: #fff;
            border-bottom: 1px solid #dee2e6;
            padding: 0.5rem 1rem;
            position: sticky;
            /* Make it sticky */
            top: 0;
            z-index: 1020;
            /* Below sidebar */
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
        }

        .course-thumbnail {
            width: 80px;
            height: 50px;
            object-fit: cover;
            margin-right: 1rem;
            border-radius: 0.25rem;
        }
    </style>
</head>

<body>

    <div class="sidebar d-none d-lg-block">
        <div class="sidebar-header">
            <a href="/" class="text-white text-decoration-none">Tên Website</a>
        </div>
        <nav class="nav flex-column">
            <a class="nav-link active" href="#dashboard"><i class="fas fa-tachometer-alt fa-fw me-2"></i> Dashboard</a>
            <a class="nav-link" href="#my-courses"><i class="fas fa-book-open fa-fw me-2"></i> Khóa học của tôi</a>
            <a class="nav-link" href="#purchase-history"><i class="fas fa-history fa-fw me-2"></i> Lịch sử mua hàng</a>
            <a class="nav-link" href="#certificates"><i class="fas fa-certificate fa-fw me-2"></i> Chứng chỉ</a>
            <a class="nav-link" href="#profile"><i class="fas fa-user-edit fa-fw me-2"></i> Chỉnh sửa Hồ sơ</a>
            <a class="nav-link" href="#support"><i class="fas fa-headset fa-fw me-2"></i> Hỗ trợ</a>
            <a class="nav-link" href="#logout"><i class="fas fa-sign-out-alt fa-fw me-2"></i> Đăng xuất</a>
        </nav>
    </div>

    <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="sidebarOffcanvas" aria-labelledby="sidebarOffcanvasLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="sidebarOffcanvasLabel">Menu</h5>
            <button type="button" class="btn-close btn-close-white text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body p-0">
            <nav class="nav flex-column">
                <a class="nav-link active" href="#dashboard"><i class="fas fa-tachometer-alt fa-fw me-2"></i> Dashboard</a>
                <a class="nav-link" href="#my-courses"><i class="fas fa-book-open fa-fw me-2"></i> Khóa học của tôi</a>
                <a class="nav-link" href="#purchase-history"><i class="fas fa-history fa-fw me-2"></i> Lịch sử mua hàng</a>
                <a class="nav-link" href="#certificates"><i class="fas fa-certificate fa-fw me-2"></i> Chứng chỉ</a>
                <a class="nav-link" href="#profile"><i class="fas fa-user-edit fa-fw me-2"></i> Chỉnh sửa Hồ sơ</a>
                <a class="nav-link" href="#support"><i class="fas fa-headset fa-fw me-2"></i> Hỗ trợ</a>
                <a class="nav-link" href="#logout"><i class="fas fa-sign-out-alt fa-fw me-2"></i> Đăng xuất</a>
            </nav>
        </div>
    </div>


    <div class="main-content">
        <div class="topbar-sm d-lg-none d-flex justify-content-between align-items-center mb-3">
            <button class="btn btn-dark" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarOffcanvas" aria-controls="sidebarOffcanvas">
                <i class="fas fa-bars"></i>
            </button>
            <h5 class="mb-0">Dashboard</h5>
            <div></div>
        </div>

        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        <i class="fas fa-bell me-2"></i> Bạn có 2 thông báo mới!
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="card-title mb-0"><i class="fas fa-book-open me-2"></i> Các khóa học đã đăng ký</h5>
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
                            <div class="d-flex align-items-center">
                                <img src="https://via.placeholder.com/80x50/dee2e6/6c757d.png?text=Course+3" alt="Course Thumbnail" class="course-thumbnail">
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
                            <img src="https://via.placeholder.com/60/0d6efd/fff.png?text=AV" alt="Avatar" class="profile-avatar mb-2">
                            <h5 class="card-title">Chào, Nguyễn Văn A!</h5>
                            <p class="card-text text-muted mb-1">Email: nguyenvana@email.com</p>
                            <p class="card-text text-muted mb-3">Ngày tham gia: 20/10/2024</p>
                            <a href="#profile" class="btn btn-outline-primary btn-sm"><i class="fas fa-user-edit me-1"></i> Chỉnh sửa Hồ sơ</a>
                        </div>
                    </div>

                    <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="card-title mb-0"><i class="fas fa-lightbulb me-2"></i> Gợi ý cho bạn</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <img src="https://via.placeholder.com/80x50/dee2e6/6c757d.png?text=Suggest+1" alt="Suggested Course" class="course-thumbnail">
                                <div class="flex-grow-1">
                                    <h6 class="mb-0 fs-sm">Khóa Học ReactJS Nâng Cao</h6>
                                    <small class="text-muted">Lập trình viên</small>
                                </div>
                                <a href="#" class="btn btn-sm btn-outline-success ms-2" title="Xem chi tiết"><i class="fas fa-arrow-right"></i></a>
                            </div>
                            <div class="d-flex align-items-center">
                                <img src="https://via.placeholder.com/80x50/dee2e6/6c757d.png?text=Suggest+2" alt="Suggested Course" class="course-thumbnail">
                                <div class="flex-grow-1">
                                    <h6 class="mb-0 fs-sm">Nghệ Thuật Nhiếp Ảnh Cơ Bản</h6>
                                    <small class="text-muted">Nhiếp ảnh</small>
                                </div>
                                <a href="#" class="btn btn-sm btn-outline-success ms-2" title="Xem chi tiết"><i class="fas fa-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <footer class="text-center text-muted mt-4">
            <small>&copy; 2025 Tên Website. All Rights Reserved.</small>
        </footer>

    </div>
    <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script> -->
    <script src="public/js/bootstrap.bundle.min.js"></script>
</body>

</html>