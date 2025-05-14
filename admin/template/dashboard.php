<div class="sidebar d-none d-lg-block">
    <div class="sidebar-header">
        <a href="admin.php" class="text-white text-decoration-none">E-Course</a>
    </div>
    <nav class="nav flex-column">
        <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'admin.php') ? 'active' : ''; ?>" href="admin.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
        <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'course-management.php') ? 'active' : ''; ?>" href="course-management.php"><i class="bi bi-book-half me-2"></i>Quản Lý Khóa Học</a>
        <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'upload-video.php') ? 'active' : ''; ?>" href="upload-video.php"><i class="bi bi-cloud-upload-fill me-2"></i>Tải lên Video bài học</a>
        <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'user-management.php') ? 'active' : ''; ?>" href="user-management.php"><i class="bi bi-people-fill me-2"></i>Quản Lý Người Dùng</a>
        <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'revenue.php') ? 'active' : ''; ?>" href="revenue.php"><i class="bi bi-bar-chart-line-fill me-2"></i>Doanh Thu</a>
        <a class="nav-link" href="#logout"><i class="bi bi-box-arrow-right me-2"></i>Đăng xuất</a>
    </nav>
</div>

<div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="sidebarOffcanvas" aria-labelledby="sidebarOffcanvasLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="sidebarOffcanvasLabel">Menu</h5>
        <button type="button" class="btn-close btn-close-white text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-0">
        <nav class="nav flex-column">
            <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'admin.php') ? 'active' : ''; ?>" href="admin.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
            <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'course-management.php') ? 'active' : ''; ?>" href="course-management.php"><i class="bi bi-book-half me-2"></i>Quản Lý Khóa Học</a>
            <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'upload-video.php') ? 'active' : ''; ?>" href="upload-video.php"><i class="bi bi-cloud-upload-fill me-2"></i>Tải lên Video bài học</a>
            <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'user-management.php') ? 'active' : ''; ?>" href="user-management.php"><i class="bi bi-people-fill me-2"></i>Quản Lý Người Dùng</a>
            <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'revenue.php') ? 'active' : ''; ?>" href="revenue.php"><i class="bi bi-bar-chart-line-fill me-2"></i>Doanh Thu</a>
            <a class="nav-link" href="#logout"><i class="bi bi-box-arrow-right me-2"></i>Đăng xuất</a>
        </nav>
    </div>
</div>