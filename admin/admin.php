<!DOCTYPE html>
<html lang="vi">

<link rel="stylesheet" href="css/admin_style.css">
<link rel="stylesheet" href="css/bootstrap.min.css">
<link rel="stylesheet" href="css/base_dashboard.css">

<body>
    <?php include('template/dashboard.php'); ?>
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
    </div>
    <script src="js/bootstrap.bundle.min.js"></script>
</body>


</html>