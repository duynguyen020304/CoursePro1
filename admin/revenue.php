<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <title>Thống kê Doanh thu</title>
    <!-- 1) Bootstrap CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <!-- 2) Dashboard base + admin overrides -->
    <link href="css/base_dashboard.css" rel="stylesheet">
    <link href="css/admin_style.css" rel="stylesheet">
</head>

<body>
    <?php include('template/dashboard.php'); ?>

    <div class="main-content">
        <div class="container-fluid py-4">

            <!-- Header + Date filter -->
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
                <h3 class="mb-0">Thống kê Doanh thu</h3>
                <form class="d-flex align-items-center" method="get" action="">
                    <label class="me-2">Từ:</label>
                    <input type="date" name="start_date" class="form-control me-3">
                    <label class="me-2">Đến:</label>
                    <input type="date" name="end_date" class="form-control me-3">
                    <button type="submit" class="btn btn-primary">Lọc</button>
                </form>
            </div>

            <!-- Summary cards -->
            <div class="row g-3 mb-4">
                <div class="col-sm-6 col-md-3">
                    <div class="card border-success h-100">
                        <div class="card-body">
                            <h6 class="card-title">Tổng Doanh thu</h6>
                            <h3 class="card-text text-success">₫<span id="totalRevenue">0</span></h3>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="card border-info h-100">
                        <div class="card-body">
                            <h6 class="card-title">Tổng Đơn hàng</h6>
                            <h3 class="card-text text-info"><span id="totalOrders">0</span></h3>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="card border-warning h-100">
                        <div class="card-body">
                            <h6 class="card-title">Giá trị TB/Đơn</h6>
                            <h3 class="card-text text-warning">₫<span id="avgOrderValue">0</span></h3>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="card border-secondary h-100">
                        <div class="card-body">
                            <h6 class="card-title">Khóa học bán chạy</h6>
                            <h5 class="card-text" id="topCourse">—</h5>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Revenue Chart -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="fas fa-chart-line me-2"></i> Doanh thu theo thời gian</h6>
                </div>
                <div class="card-body">
                    <canvas id="revenueChart" height="100"></canvas>
                </div>
            </div>

            <!-- Recent Orders Table -->
            <div class="card">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="fas fa-list me-2"></i> Đơn hàng mới nhất</h6>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>OrderID</th>
                                <th>Ngày</th>
                                <th>Khách hàng</th>
                                <th>Tổng tiền (₫)</th>
                                <th>Trạng thái</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Ví dụ PHP loop:
                            // $orders = fetchRecentOrders();
                            // foreach ($orders as $o) {
                            //   echo "<tr>";
                            //   echo "<td>{$o['OrderID']}</td>";
                            //   echo "<td>{$o['OrderDate']}</td>";
                            //   echo "<td>{$o['UserName']}</td>";
                            //   echo "<td>{$o['TotalAmount']}</td>";
                            //   echo "<td>{$o['Status']}</td>";
                            //   echo "<td><a href='order_detail.php?id={$o['OrderID']}' class='btn btn-sm btn-outline-primary'>Xem</a></td>";
                            //   echo "</tr>";
                            // }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <!-- Scripts -->
    <!-- Chart.js (bạn có thể dùng CDN hoặc local) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="js/bootstrap.bundle.min.js"></script>
    <script>
        // Ví dụ render Chart.js
        const ctx = document.getElementById('revenueChart').getContext('2d');
        const revenueChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [], // ['2025-04-01', ...]
                datasets: [{
                    label: 'Doanh thu',
                    data: [], // [1200000, ...]
                    fill: false,
                    tension: 0.1
                }]
            },
            options: {
                scales: {
                    x: {
                        display: true
                    },
                    y: {
                        display: true,
                        ticks: {
                            callback: value => new Intl.NumberFormat('vi-VN').format(value)
                        }
                    }
                }
            }
        });

        // TODO: AJAX/Fetch để load:
        // - Tổng doanh thu, tổng đơn hàng, giá trị TB, khóa học bán chạy
        // - Dữ liệu chart (labels + data)
        // - Bảng đơn hàng
    </script>
</body>

</html>