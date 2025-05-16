<?php
// template/user_sidebar.php
// Biến $current_page được include từ file gọi.
if (!isset($current_page)) {
    $current_page = '';
}
?>

<link href="public/css/sidebar.css" rel="stylesheet">
<!-- Desktop sidebar with collapse -->
<div id="sidebar" class="sidebar d-none d-lg-block">
    <div class="sidebar-header d-flex align-items-center justify-content-between px-3 py-2">
        <a href="home.php" class="text-white text-decoration-none">Ecourse</a>
        <button type="button" id="sidebarCollapse" class="btn btn-sm btn-toggle" aria-label="Thu gọn sidebar">
            <i class="bi bi-chevron-left"></i>
        </button>
    </div>
    <nav class="nav flex-column px-2">
        <a class="nav-link <?php echo ($current_page == 'user.php')             ? 'active' : ''; ?>" href="user.php">
            <i class="bi bi-speedometer2 me-2"></i> Dashboard
        </a>
        <a class="nav-link <?php echo ($current_page == 'purchase-history.php') ? 'active' : ''; ?>" href="purchase-history.php">
            <i class="bi bi-clock-history me-2"></i> Lịch sử mua hàng
        </a>
        <a class="nav-link <?php echo ($current_page == 'certificates.php')      ? 'active' : ''; ?>" href="certificates.php">
            <i class="bi bi-patch-check me-2"></i> Chứng chỉ
        </a>
        <a class="nav-link <?php echo ($current_page == 'edit-profile.php')      ? 'active' : ''; ?>" href="edit-profile.php">
            <i class="bi bi-pencil-square me-2"></i> Chỉnh sửa Hồ sơ
        </a>
        <a class="nav-link" href="logout.php">
            <i class="bi bi-box-arrow-right me-2"></i> Đăng xuất
        </a>
    </nav>
</div>

<!-- Offcanvas mobile menu -->
<div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="sidebarOffcanvas" aria-labelledby="sidebarOffcanvasLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title text-white" id="sidebarOffcanvasLabel">Menu</h5>
        <button type="button" class="btn-close btn-close-white text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-0">
        <nav class="nav flex-column px-2">
            <a class="nav-link <?php echo ($current_page == 'user.php')             ? 'active' : ''; ?>" href="user.php">
                <i class="bi bi-speedometer2 me-2"></i> Dashboard
            </a>
            <a class="nav-link <?php echo ($current_page == 'purchase-history.php') ? 'active' : ''; ?>" href="purchase-history.php">
                <i class="bi bi-clock-history me-2"></i> Lịch sử mua hàng
            </a>
            <a class="nav-link <?php echo ($current_page == 'certificates.php')      ? 'active' : ''; ?>" href="certificates.php">
                <i class="bi bi-patch-check me-2"></i> Chứng chỉ
            </a>
            <a class="nav-link <?php echo ($current_page == 'edit-profile.php')      ? 'active' : ''; ?>" href="edit-profile.php">
                <i class="bi bi-pencil-square me-2"></i> Chỉnh sửa Hồ sơ
            </a>
            <a class="nav-link" href="logout.php">
                <i class="bi bi-box-arrow-right me-2"></i> Đăng xuất
            </a>
        </nav>
    </div>
</div>

<!-- Script for sidebar collapse -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('sidebar');
        const toggleBtn = document.getElementById('sidebarCollapse');
        if (!sidebar || !toggleBtn) return;

        // Load saved state
        if (localStorage.getItem('sidebarCollapsed') === 'true') {
            sidebar.classList.add('collapsed');
        }

        // Toggle on click
        toggleBtn.addEventListener('click', function() {
            sidebar.classList.toggle('collapsed');
            localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
        });
    });
    // Kích hoạt tooltip cho nav-link
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (el) {
        return new bootstrap.Tooltip(el);
    });

</script>
