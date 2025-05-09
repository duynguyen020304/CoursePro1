<?php include('template/head.php'); ?>
<link href="public/css/cart.css" rel="stylesheet">
<?php include('template/header.php'); ?>

<!-- Giỏ hàng -->
<section class="cart-section py-5">
    <div class="container">
        <h2 class="text-center mb-4">Các Khóa Học Trong Giỏ Hàng</h2>
        <div class="row">
            <div class="col-md-8">
                <div class="cart-items">
                    <!-- Khóa học 1 -->
                    <div class="cart-item">
                        <div class="row">
                            <div class="col-md-3">
                                <img src="media/course1.jpg" alt="Khóa học 1" class="img-fluid">
                            </div>
                            <div class="col-md-6">
                                <h5 class="cart-item-title">Lập trình Web</h5>
                                <p>Giảng viên: John Doe</p>
                            </div>
                            <div class="col-md-3">
                                <p class="cart-item-price">799.000 VNĐ</p>
                                <input type="number" class="form-control" value="1" min="1">
                                <button class="btn btn-danger btn-sm mt-2">Xóa</button>
                            </div>
                        </div>
                    </div>
                    <!-- Khóa học 2 -->
                    <div class="cart-item">
                        <div class="row">
                            <div class="col-md-3">
                                <img src="media/course2.jpg" alt="Khóa học 2" class="img-fluid">
                            </div>
                            <div class="col-md-6">
                                <h5 class="cart-item-title">Thiết kế Đồ họa</h5>
                                <p>Giảng viên: Jane Smith</p>
                            </div>
                            <div class="col-md-3">
                                <p class="cart-item-price">650.000 VNĐ</p>
                                <input type="number" class="form-control" value="1" min="1">
                                <button class="btn btn-danger btn-sm mt-2">Xóa</button>
                            </div>
                        </div>
                    </div>
                    <!-- Khóa học 3 -->
                    <div class="cart-item">
                        <div class="row">
                            <div class="col-md-3">
                                <img src="media/course3.jpg" alt="Khóa học 3" class="img-fluid">
                            </div>
                            <div class="col-md-6">
                                <h5 class="cart-item-title">Marketing Online</h5>
                                <p>Giảng viên: Mark Lee</p>
                            </div>
                            <div class="col-md-3">
                                <p class="cart-item-price">1.200.000 VNĐ</p>
                                <input type="number" class="form-control" value="1" min="1">
                                <button class="btn btn-danger btn-sm mt-2">Xóa</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="cart-summary">
                    <h4>Tổng Cộng</h4>
                    <ul class="list-unstyled">
                        <li><strong>Tổng Tiền:</strong> <span id="total-price">2.649.000 VNĐ</span></li>
                    </ul>
                    <a href="checkout.php" class="btn btn-success btn-lg btn-block">Tiến Hành Thanh Toán</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Phần Gợi Ý Khóa Học -->
<section class="recommended-courses py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-4">Khóa Học Gợi Ý Cho Bạn</h2>
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <img src="media/recommended1.jpg" class="card-img-top" alt="Khóa học Gợi Ý 1">
                    <div class="card-body">
                        <h5 class="card-title">Lập Trình Python</h5>
                        <p class="card-text">Khóa học Python cho người mới bắt đầu.</p>
                        <a href="#" class="btn btn-primary">Xem Chi Tiết</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <img src="media/recommended2.jpg" class="card-img-top" alt="Khóa học Gợi Ý 2">
                    <div class="card-body">
                        <h5 class="card-title">Thiết Kế Web</h5>
                        <p class="card-text">Khóa học thiết kế giao diện web với HTML, CSS, JavaScript.</p>
                        <a href="#" class="btn btn-primary">Xem Chi Tiết</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <img src="media/recommended3.jpg" class="card-img-top" alt="Khóa học Gợi Ý 3">
                    <div class="card-body">
                        <h5 class="card-title">Digital Marketing</h5>
                        <p class="card-text">Khóa học marketing trực tuyến cho doanh nghiệp.</p>
                        <a href="#" class="btn btn-primary">Xem Chi Tiết</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include('template/footer.php'); ?>