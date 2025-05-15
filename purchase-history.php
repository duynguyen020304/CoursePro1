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
    <link href="public/css/purchase-history.css" rel="stylesheet">
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