<?php
// File này được include, biến $current_page được truyền từ file gọi nó.
// Nếu $current_page chưa được set, đặt một giá trị mặc định để tránh lỗi.
if (!isset($current_page)) {
    $current_page = ''; // Hoặc trang mặc định ví dụ 'user.php'
}
?>
<div class="sidebar d-none d-lg-block">
    <div class="sidebar-header">
        <a href="home.php" class="text-white text-decoration-none">Ecourse</a>
    </div>
    <nav class="nav flex-column">
        <a class="nav-link <?php echo ($current_page == 'user.php') ? 'active' : ''; ?>" href="user.php"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
        <a class="nav-link <?php echo ($current_page == 'purchase-history.php') ? 'active' : ''; ?>" href="purchase-history.php"><i class="bi bi-clock-history me-2"></i> Lịch sử mua hàng</a>
        <a class="nav-link <?php echo ($current_page == 'certificates.php') ? 'active' : ''; ?>" href="certificates.php"><i class="bi bi-patch-check me-2"></i> Chứng chỉ</a>
        <a class="nav-link <?php echo ($current_page == 'edit-profile.php') ? 'active' : ''; ?>" href="edit-profile.php"><i class="bi bi-pencil-square me-2"></i> Chỉnh sửa Hồ sơ</a>
        <a class="nav-link <?php echo ($current_page == 'support.php') ? 'active' : ''; ?>" href="support.php"><i class="bi bi-headset me-2"></i> Hỗ trợ</a>
        <a class="nav-link" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i> Đăng xuất</a>
    </nav>
</div>

<div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="sidebarOffcanvas" aria-labelledby="sidebarOffcanvasLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="sidebarOffcanvasLabel">Menu</h5>
        <button type="button" class="btn-close btn-close-white text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-0">
        <nav class="nav flex-column">
            <a class="nav-link <?php echo ($current_page == 'user.php') ? 'active' : ''; ?>" href="user.php"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
            <a class="nav-link <?php echo ($current_page == 'purchase-history.php') ? 'active' : ''; ?>" href="purchase-history.php"><i class="bi bi-clock-history me-2"></i> Lịch sử mua hàng</a>
            <a class="nav-link <?php echo ($current_page == 'certificates.php') ? 'active' : ''; ?>" href="certificates.php"><i class="bi bi-patch-check me-2"></i> Chứng chỉ</a>
            <a class="nav-link <?php echo ($current_page == 'edit-profile.php') ? 'active' : ''; ?>" href="edit-profile.php"><i class="bi bi-pencil-square me-2"></i> Chỉnh sửa Hồ sơ</a>
            <a class="nav-link <?php echo ($current_page == 'support.php') ? 'active' : ''; ?>" href="support.php"><i class="bi bi-headset me-2"></i> Hỗ trợ</a>
            <a class="nav-link" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i> Đăng xuất</a>
        </nav>
    </div>
</div>