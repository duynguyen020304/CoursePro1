<?php include('template/head.php'); ?>
<link href="public/css/course.css" rel="stylesheet">
<?php include('template/header.php'); ?>

<!-- Banner -->
<header class="hero-section" style="background-image: url('media/course-bg.jpg'); background-size: cover; padding: 100px 0; color: #fff;">
    <div class="container text-center">
        <h1>Khám Phá Các Khóa Học</h1>
        <p>Học từ các chuyên gia và nâng cao kỹ năng nghề nghiệp của bạn ngay hôm nay!</p>
    </div>
</header>

<!-- Tìm kiếm và lọc khóa học -->
<section class="search-section py-5">
    <div class="container">
        <h2 class="text-center mb-4">Tìm Kiếm và Lọc Khóa Học</h2>
        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <label for="category">Danh Mục Khóa Học</label>
                    <select id="category" class="form-control">
                        <option>Chọn Danh Mục</option>
                        <option>Công nghệ thông tin</option>
                        <option>Thiết kế</option>
                        <option>Marketing</option>
                        <option>Kinh doanh</option>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="price">Khoảng Giá</label>
                    <select id="price" class="form-control">
                        <option>Chọn Khoảng Giá</option>
                        <option>Dưới 500.000 VNĐ</option>
                        <option>500.000 VNĐ - 1.000.000 VNĐ</option>
                        <option>Trên 1.000.000 VNĐ</option>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="search">Tìm Kiếm</label>
                    <input type="text" id="search" class="form-control" placeholder="Tìm khóa học...">
                </div>
            </div>
            <div class="col-md-3">
                <button class="btn btn-primary btn-lg mt-4">Tìm Kiếm</button>
            </div>
        </div>
    </div>
</section>

<!-- Danh sách khóa học -->
<section class="course-list py-5">
    <div class="container">
        <h2 class="text-center mb-4">Danh Sách Các Khóa Học</h2>
        <div class="row">
            <div class="col-md-4">
                <div class="card course-card">
                    <img src="media/course1.jpg" class="card-img-top" alt="Khóa học 1">
                    <div class="card-body">
                        <h5 class="card-title">Lập trình Web</h5>
                        <p class="card-text">Giảng viên: John Doe</p>
                        <p class="card-price">Giá: 799.000 VNĐ</p>
                        <a href="course-detail.php" class="btn btn-primary">Xem Chi Tiết</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card course-card">
                    <img src="media/course2.jpg" class="card-img-top" alt="Khóa học 2">
                    <div class="card-body">
                        <h5 class="card-title">Thiết kế Đồ họa</h5>
                        <p class="card-text">Giảng viên: Jane Smith</p>
                        <p class="card-price">Giá: 650.000 VNĐ</p>
                        <a href="course-detail.php" class="btn btn-primary">Xem Chi Tiết</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card course-card">
                    <img src="media/course3.jpg" class="card-img-top" alt="Khóa học 3">
                    <div class="card-body">
                        <h5 class="card-title">Marketing Online</h5>
                        <p class="card-text">Giảng viên: Mark Lee</p>
                        <p class="card-price">Giá: 1.200.000 VNĐ</p>
                        <a href="course-detail.php" class="btn btn-primary">Xem Chi Tiết</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include('template/footer.php'); ?>
