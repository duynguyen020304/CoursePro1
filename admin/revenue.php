<?php
// (Tạm thời để trống phần PHP, bạn sẽ cần thêm logic để lấy dữ liệu doanh thu thực tế)

// Giả sử bạn có hàm để lấy dữ liệu dựa trên bộ lọc ngày tháng
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : null;
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : null;

// Ví dụ dữ liệu (bạn sẽ thay thế bằng logic lấy dữ liệu từ database dựa trên $start_date, $end_date)
$totalRevenue = 125800000; // VND
$totalOrders = 75;
$avgOrderValue = ($totalOrders > 0) ? ($totalRevenue / $totalOrders) : 0;
$topCourse = "Khóa Học Lập Trình Web Toàn Diện";

$recentOrders = [
    ['OrderID' => 'DH00123', 'OrderDate' => '2025-05-10', 'UserName' => 'Nguyễn Văn An', 'TotalAmount' => 1200000, 'Status' => 'Hoàn thành'],
    ['OrderID' => 'DH00122', 'OrderDate' => '2025-05-10', 'UserName' => 'Trần Thị Bình', 'TotalAmount' => 950000, 'Status' => 'Đang xử lý'],
    ['OrderID' => 'DH00121', 'OrderDate' => '2025-05-09', 'UserName' => 'Lê Văn Cường', 'TotalAmount' => 1500000, 'Status' => 'Hoàn thành'],
    ['OrderID' => 'DH00120', 'OrderDate' => '2025-05-08', 'UserName' => 'Phạm Thị Dung', 'TotalAmount' => 750000, 'Status' => 'Đã hủy'],
];

// Dữ liệu ví dụ cho biểu đồ (theo ngày trong 1 tuần)
$chartLabels = ['04/05', '05/05', '06/05', '07/05', '08/05', '09/05', '10/05'];
$chartData = [12000000, 19000000, 15000000, 22000000, 18000000, 25000000, 30000000];

// TODO: Viết logic PHP để truy vấn CSDL và tính toán các giá trị trên dựa vào $start_date và $end_date
// Nếu $start_date và $end_date được cung cấp, bạn sẽ cần điều chỉnh các truy vấn SQL của mình
// để lọc dữ liệu trong khoảng thời gian đó.
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <title>Thống kê Doanh thu</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="css/base_dashboard.css" rel="stylesheet">
    <link href="css/admin_style.css" rel="stylesheet">
    <style>
        .summary-card .card-title {
            font-size: 0.9rem;
            color: #6c757d;
            margin-bottom: 0.25rem;
        }
        .summary-card .card-text {
            font-size: 1.75rem;
            font-weight: bold;
        }
        .summary-card .card-text.small-text { /* Cho khóa học bán chạy */
            font-size: 1.1rem;
            font-weight: 500;
        }
         .action-buttons .btn {
            margin-right: 0.25rem;
        }
        .action-buttons .btn:last-child {
            margin-right: 0;
        }
    </style>
</head>

<body>
    <?php include('template/dashboard.php'); ?>

    <div class="main-content">
        <div class="container-fluid py-4">

            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
                <h3 class="mb-0"><i class="bi bi-cash-stack me-2"></i>Thống kê Doanh thu</h3>
                <form class="d-flex align-items-center" method="get" action="">
                    <label for="start_date_filter" class="me-2 form-label mb-0">Từ:</label>
                    <input type="date" id="start_date_filter" name="start_date" class="form-control me-3" value="<?= htmlspecialchars($start_date ?? '') ?>">
                    <label for="end_date_filter" class="me-2 form-label mb-0">Đến:</label>
                    <input type="date" id="end_date_filter" name="end_date" class="form-control me-3" value="<?= htmlspecialchars($end_date ?? '') ?>">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-funnel-fill me-1"></i> Lọc</button>
                </form>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-sm-6 col-lg-3">
                    <div class="card border-success h-100 shadow-sm summary-card">
                        <div class="card-body">
                            <h6 class="card-title text-uppercase">Tổng Doanh thu</h6>
                            <h3 class="card-text text-success">₫<span id="totalRevenue"><?= number_format($totalRevenue, 0, ',', '.') ?></span></h3>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card border-info h-100 shadow-sm summary-card">
                        <div class="card-body">
                            <h6 class="card-title text-uppercase">Tổng Đơn hàng</h6>
                            <h3 class="card-text text-info"><span id="totalOrders"><?= htmlspecialchars($totalOrders) ?></span></h3>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card border-warning h-100 shadow-sm summary-card">
                        <div class="card-body">
                            <h6 class="card-title text-uppercase">Giá trị TB/Đơn</h6>
                            <h3 class="card-text text-warning">₫<span id="avgOrderValue"><?= number_format($avgOrderValue, 0, ',', '.') ?></span></h3>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card border-secondary h-100 shadow-sm summary-card">
                        <div class="card-body">
                            <h6 class="card-title text-uppercase">Khóa học bán chạy</h6>
                            <h5 class="card-text small-text" id="topCourse"><?= htmlspecialchars($topCourse) ?></h5>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-bar-chart-line-fill me-2"></i> Doanh thu theo thời gian</h6>
                </div>
                <div class="card-body">
                    <canvas id="revenueChart" height="100"></canvas>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-table me-2"></i> Đơn hàng mới nhất</h6>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Mã ĐH</th>
                                <th>Ngày Đặt</th>
                                <th>Khách hàng</th>
                                <th class="text-end">Tổng tiền (₫)</th>
                                <th class="text-center">Trạng thái</th>
                                <th class="text-end">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (!empty($recentOrders)) {
                                foreach ($recentOrders as $o) {
                                    $statusBadge = '';
                                    switch (strtolower($o['Status'])) {
                                        case 'hoàn thành': $statusBadge = 'bg-success'; break;
                                        case 'đang xử lý': $statusBadge = 'bg-info'; break;
                                        case 'đã hủy': $statusBadge = 'bg-danger'; break;
                                        default: $statusBadge = 'bg-secondary'; break;
                                    }
                                  echo "<tr>";
                                  echo "<td>" . htmlspecialchars($o['OrderID']) . "</td>";
                                  echo "<td>" . date('d/m/Y', strtotime($o['OrderDate'])) . "</td>";
                                  echo "<td>" . htmlspecialchars($o['UserName']) . "</td>";
                                  echo "<td class='text-end'>" . number_format($o['TotalAmount'], 0, ',', '.') . "</td>";
                                  echo "<td class='text-center'><span class='badge rounded-pill " . $statusBadge . "'>" . htmlspecialchars($o['Status']) . "</span></td>";
                                  echo "<td class='text-end action-buttons'>
                                            <a href='order_detail.php?id={$o['OrderID']}' class='btn btn-sm btn-outline-primary' title='Xem chi tiết'>
                                                <i class='bi bi-eye-fill'></i>
                                            </a>
                                        </td>";
                                  echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='6' class='text-center'>Chưa có đơn hàng nào.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
         <footer class="text-center text-muted mt-4 py-3 border-top">
            <small>&copy; <?= date('Y') ?> Tên Website. All Rights Reserved.</small>
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('revenueChart').getContext('2d');
            const revenueChart = new Chart(ctx, {
                type: 'line', // hoặc 'bar'
                data: {
                    labels: <?php echo json_encode($chartLabels); ?>, // Lấy từ PHP
                    datasets: [{
                        label: 'Doanh thu',
                        data: <?php echo json_encode($chartData); ?>, // Lấy từ PHP
                        borderColor: 'rgba(75, 192, 192, 1)',
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        fill: true, // Tô màu vùng dưới đường line
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false, // Cho phép tùy chỉnh chiều cao
                    scales: {
                        x: {
                            display: true,
                            title: {
                                display: true,
                                text: 'Thời gian'
                            }
                        },
                        y: {
                            display: true,
                            title: {
                                display: true,
                                text: 'Doanh thu (₫)'
                            },
                            ticks: {
                                callback: value => new Intl.NumberFormat('vi-VN').format(value)
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed.y !== null) {
                                        label += new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(context.parsed.y);
                                    }
                                    return label;
                                }
                            }
                        }
                    }
                }
            });

            // Cập nhật các thẻ thống kê nếu bạn không render trực tiếp bằng PHP
            // (ví dụ nếu bạn fetch dữ liệu bằng AJAX sau khi trang tải)
            // document.getElementById('totalRevenue').textContent = new Intl.NumberFormat('vi-VN').format(<?php echo $totalRevenue; ?>);
            // document.getElementById('totalOrders').textContent = <?php echo $totalOrders; ?>;
            // document.getElementById('avgOrderValue').textContent = new Intl.NumberFormat('vi-VN').format(<?php echo $avgOrderValue; ?>);
            // document.getElementById('topCourse').textContent = '<?php echo htmlspecialchars($topCourse); ?>';
        });
    </script>
</body>
</html>