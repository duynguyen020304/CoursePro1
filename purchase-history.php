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

// Dữ liệu mẫu cho purchase history và user
$purchaseHistory = [
    ['OrderID' => 'DH001', 'CourseName' => 'Khóa Học Lập Trình Web Cơ Bản', 'PurchaseDate' => '2024-12-01', 'Price' => 599000, 'InvoiceLink' => '#link-to-invoice-001'],
    ['OrderID' => 'DH002', 'CourseName' => 'Thiết Kế Giao Diện UI/UX Nâng Cao', 'PurchaseDate' => '2025-01-15', 'Price' => 799000, 'InvoiceLink' => '#link-to-invoice-002'],
    ['OrderID' => 'DH003', 'CourseName' => 'Marketing Online Từ A đến Z', 'PurchaseDate' => '2025-03-20', 'Price' => 1299000, 'InvoiceLink' => '#link-to-invoice-003'],
    ['OrderID' => 'DH004', 'CourseName' => 'Nghệ Thuật Nhiếp Ảnh Cơ Bản', 'PurchaseDate' => '2025-04-05', 'Price' => 499000, 'InvoiceLink' => '#link-to-invoice-004'],
];
$user = (object) [
    'name' => isset($_SESSION['user']['name']) ? $_SESSION['user']['name'] : 'Người dùng',
];

// Xác định trang hiện tại để active menu
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch sử mua hàng - Ecourse</title>
    <link href="public/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        /* TOÀN BỘ CSS GỐC CỦA BẠN TỪ FILE purchase-history.php TRƯỚC ĐÓ SẼ NẰM Ở ĐÂY */
        body { background-color: #f8f9fa; }
        .sidebar { width: 260px; height: 100vh; position: fixed; top: 0; left: 0; background-color: #343a40; padding-top: 1rem; transition: all 0.3s; z-index: 1030; }
        .sidebar .nav-link { color: #adb5bd; padding: 0.75rem 1.5rem; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { color: #fff; background-color: #495057; }
        .sidebar .sidebar-header { color: #fff; padding: 0 1.5rem 1rem 1.5rem; font-size: 1.2rem; font-weight: bold; }
        .main-content { transition: margin-left 0.3s; padding: 1.5rem; overflow-x: hidden; }
        @media (min-width: 992px) { .main-content { margin-left: 260px; } .navbar-toggler-icon { display: none !important; } }
        .offcanvas-start { width: 260px; background-color: #343a40; }
        .offcanvas-header { border-bottom: 1px solid #495057; }
        .offcanvas-title { color: #fff; }
        .offcanvas-body .nav-link { color: #adb5bd; }
        .offcanvas-body .nav-link:hover, .offcanvas-body .nav-link.active { color: #fff; background-color: #495057; }
        .btn-close-white { filter: invert(1) grayscale(100%) brightness(200%); }
        .topbar-sm { background-color: #fff; border-bottom: 1px solid #dee2e6; padding: 0.5rem 1rem; position: sticky; top: 0; z-index: 1020; }
        .nav-link i { vertical-align: middle; }
        .table th { font-weight: 500; }
        .table td, .table th { vertical-align: middle; }
        .badge.bg-success-light { background-color: var(--bs-success-bg-subtle); color: var(--bs-success-text-emphasis); }
        .badge.bg-warning-light { background-color: var(--bs-warning-bg-subtle); color: var(--bs-warning-text-emphasis); }
        .card { margin-bottom: 1.5rem; } /* Thêm lại style card nếu cần */
    </style>
</head>
<body>

    <?php include('template/user_sidebar.php'); // Include template sidebar đã sửa ?>

    <div class="main-content">
        <div class="topbar-sm d-lg-none d-flex justify-content-between align-items-center mb-3">
            <button class="btn btn-dark" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarOffcanvas" aria-controls="sidebarOffcanvas">
                <i class="bi bi-list"></i>
            </button>
            <h5 class="mb-0">Lịch sử mua hàng</h5>
            <div></div>
        </div>

        <div class="container-fluid">
            <h3 class="mb-4 pt-2 pt-lg-0">Lịch sử mua hàng</h3>

            <div class="card shadow-sm">
                <div class="card-body">
                    <?php if (!empty($purchaseHistory)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Mã đơn hàng</th>
                                        <th>Tên khóa học</th>
                                        <th>Ngày mua</th>
                                        <th class="text-end">Giá (₫)</th>
                                        <th class="text-center">Hóa đơn</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($purchaseHistory as $item): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($item['OrderID']) ?></td>
                                        <td><?= htmlspecialchars($item['CourseName']) ?></td>
                                        <td><?= date('d/m/Y', strtotime($item['PurchaseDate'])) ?></td>
                                        <td class="text-end"><?= number_format($item['Price'], 0, ',', '.') ?></td>
                                        <td class="text-center">
                                            <a href="<?= htmlspecialchars($item['InvoiceLink']) ?>" class="btn btn-sm btn-outline-primary" title="Xem hóa đơn">
                                                <i class="bi bi-receipt-cutoff"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-light text-center" role="alert">
                            <i class="bi bi-cart-x fs-3 d-block mb-2"></i>
                            Bạn chưa có lịch sử mua hàng nào.
                            <br>
                            <a href="/all-courses.php" class="btn btn-primary mt-3">Khám phá khóa học</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
             <?php if (!empty($purchaseHistory) && count($purchaseHistory) > 5 ): ?>
            <nav aria-label="Purchase history pagination" class="mt-4 d-flex justify-content-center">
                <ul class="pagination">
                    <li class="page-item disabled"><a class="page-link" href="#">Trước</a></li>
                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                    <li class="page-item"><a class="page-link" href="#">2</a></li>
                    <li class="page-item"><a class="page-link" href="#">Sau</a></li>
                </ul>
            </nav>
            <?php endif; ?>
        </div>

        <footer class="text-center text-muted mt-4 py-3 border-top">
            <small>&copy; <?= date('Y') ?> Tên Website. All Rights Reserved.</small>
        </footer>
    </div>
    <script src="public/js/bootstrap.bundle.min.js"></script>
</body>
</html>