<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <title>Quản lý Khóa học</title>
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

            <!-- Header + Add Button -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="mb-0">Quản lý Khóa học</h3>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#courseModal">
                    <i class="fas fa-plus me-1"></i> Thêm khóa học
                </button>
            </div>

            <!-- Courses Table -->
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Tiêu đề</th>
                            <th>Giá</th>
                            <th>Giảng viên</th>
                            <th>Danh mục</th>
                            <th>Ngày tạo</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Ví dụ PHP loop:
                        // $courses = fetchAllCourses();
                        // foreach ($courses as $i => $c) {
                        //   echo "<tr>";
                        //   echo "<td>".($i+1)."</td>";
                        //   echo "<td>{$c['Title']}</td>";
                        //   echo "<td>{$c['Price']}</td>";
                        //   echo "<td>{$c['InstructorName']}</td>";
                        //   echo "<td>{$c['CategoryList']}</td>";
                        //   echo "<td>{$c['CreatedDate']}</td>";
                        //   echo "<td>
                        //           <button class='btn btn-sm btn-outline-secondary me-1 edit-course' data-id='{$c['CourseID']}'>Sửa</button>
                        //           <button class='btn btn-sm btn-outline-danger delete-course' data-id='{$c['CourseID']}'>Xóa</button>
                        //         </td>";
                        //   echo "</tr>";
                        // }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal: Thêm / Sửa Khóa học -->
    <div class="modal fade" id="courseModal" tabindex="-1" aria-labelledby="courseModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="courseForm" method="post" action="course_save.php" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title" id="courseModalLabel">Thêm Khóa học</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="CourseID" id="CourseID">

                        <div class="row g-3">
                            <div class="col-md-8">
                                <label for="Title" class="form-label">Tiêu đề</label>
                                <input type="text" class="form-control" id="Title" name="Title" required>
                            </div>
                            <div class="col-md-4">
                                <label for="Price" class="form-label">Giá (₫)</label>
                                <input type="number" step="0.01" class="form-control" id="Price" name="Price" required>
                            </div>

                            <div class="col-md-6">
                                <label for="Instructor" class="form-label">Giảng viên</label>
                                <input type="text" class="form-control" id="Title" name="Title" required>
                            </div>
                            <div class="col-md-6">
                                <label for="Categories" class="form-label">Danh mục</label>
                                <select class="form-select" id="Categories" name="Categories[]" multiple required>
                                    <?php
                                    // foreach (fetchCategories() as $cat) {
                                    //   echo "<option value='{$cat['CategoryID']}'>{$cat['Name']}</option>";
                                    // }
                                    ?>
                                </select>
                            </div>

                            <div class="col-12">
                                <label for="Description" class="form-label">Mô tả</label>
                                <textarea class="form-control" id="Description" name="Description" rows="4"></textarea>
                            </div>

                            <div class="col-12">
                                <label for="CourseImage" class="form-label">Chọn ảnh đại diện Khóa học</label>
                                <input type="file" class="form-control" id="CourseImage" name="CourseImage">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary">Lưu</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="js/bootstrap.bundle.min.js"></script>
    <script>
        // TODO: Thêm JS để load data lên modal khi edit, gửi AJAX, confirm delete, v.v.
    </script>
</body>

</html>