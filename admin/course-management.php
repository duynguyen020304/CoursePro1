<?php
// course-management.php
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <title>Quản lý Khóa học</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="css/base_dashboard.css" rel="stylesheet">
    <link href="css/admin_style.css" rel="stylesheet">
    <style>
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

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="mb-0">Quản lý Khóa học</h3>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#courseModal">
                    <i class="bi bi-plus-lg me-1"></i> Thêm khóa học
                </button>
            </div>

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
                            <th class="text-end">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // TODO: Thay bằng dữ liệu thực từ DB
                        $exampleCourses = [
                            ['CourseID' => 1, 'Title' => 'Lập trình Web Nâng Cao với PHP và MySQL', 'Price' => 1200000, 'InstructorName' => 'Nguyễn Văn B', 'CategoryList' => 'Lập trình, Web', 'CreatedDate' => '2025-01-15'],
                            ['CourseID' => 2, 'Title' => 'Thiết kế UI/UX cho ứng dụng di động', 'Price' => 950000, 'InstructorName' => 'Trần Thị C', 'CategoryList' => 'Thiết kế, Mobile', 'CreatedDate' => '2025-02-20'],
                            ['CourseID' => 3, 'Title' => 'Marketing Online Toàn Tập', 'Price' => 1500000, 'InstructorName' => 'Lê Văn D', 'CategoryList' => 'Marketing, Kinh doanh', 'CreatedDate' => '2025-03-10'],
                        ];

                        if (!empty($exampleCourses)) {
                            foreach ($exampleCourses as $i => $c) {
                                echo "<tr>";
                                echo "<td>".($i+1)."</td>";
                                echo "<td>".htmlspecialchars($c['Title'])."</td>";
                                echo "<td>".number_format($c['Price'],0,',','.')." ₫</td>";
                                echo "<td>".htmlspecialchars($c['InstructorName'])."</td>";
                                echo "<td>".htmlspecialchars($c['CategoryList'])."</td>";
                                echo "<td>".date('d/m/Y', strtotime($c['CreatedDate']))."</td>";
                                echo "<td class='text-end action-buttons'>
                                        <button class='btn btn-sm btn-outline-primary edit-course' data-id='{$c['CourseID']}' data-bs-toggle='modal' data-bs-target='#courseModal' title='Sửa'>
                                            <i class='bi bi-pencil-square'></i>
                                        </button>
                                        <button class='btn btn-sm btn-outline-danger delete-course' data-id='{$c['CourseID']}' title='Xóa'>
                                            <i class='bi bi-trash3-fill'></i>
                                        </button>
                                      </td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='7' class='text-center'>Chưa có khóa học nào.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <nav aria-label="Course pagination" class="mt-4 d-flex justify-content-center">
                <ul class="pagination">
                    <li class="page-item disabled"><a class="page-link" href="#">Trước</a></li>
                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                    <li class="page-item"><a class="page-link" href="#">2</a></li>
                    <li class="page-item"><a class="page-link" href="#">3</a></li>
                    <li class="page-item"><a class="page-link" href="#">Sau</a></li>
                </ul>
            </nav>

        </div>
    </div>

    <!-- Modal Thêm/Chỉnh sửa Khóa học -->
    <div class="modal fade" id="courseModal" tabindex="-1" aria-labelledby="courseModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <form id="courseForm" method="post" action="course_save.php" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title" id="courseModalLabel">Thêm Khóa học</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="CourseID" id="modalCourseID">

                        <div class="row g-3">
                            <div class="col-md-8">
                                <label for="modalTitle" class="form-label">Tiêu đề khóa học <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="modalTitle" name="Title" required>
                            </div>
                            <div class="col-md-4">
                                <label for="modalPrice" class="form-label">Giá (₫) <span class="text-danger">*</span></label>
                                <input type="number" step="1000" min="0" class="form-control" id="modalPrice" name="Price" required>
                            </div>
                            <div class="col-md-6">
                                <label for="modalInstructor" class="form-label">Giảng viên <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="modalInstructor" name="InstructorName" required>
                            </div>
                            <div class="col-md-6">
                                <label for="modalCategories" class="form-label">Danh mục <span class="text-danger">*</span></label>
                                <select class="form-select" id="modalCategories" name="Categories[]" multiple required>
                                    <!-- JS sẽ tự động chèn <option> phân cấp tại đây -->
                                </select>
                                <small class="form-text text-muted">Giữ Ctrl (hoặc Cmd trên Mac) để chọn nhiều danh mục.</small>
                            </div>
                            <div class="col-12">
                                <label for="modalDescription" class="form-label">Mô tả chi tiết</label>
                                <textarea class="form-control" id="modalDescription" name="Description" rows="5"></textarea>
                            </div>
                            <div class="col-12">
                                <label for="modalCourseImage" class="form-label">Ảnh đại diện Khóa học</label>
                                <input type="file" class="form-control" id="modalCourseImage" name="CourseImage" accept="image/*">
                                <img id="modalImagePreview" src="#" alt="Xem trước ảnh" class="mt-2 img-fluid rounded" style="max-height: 150px; display: none;">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-save-fill me-1"></i> Lưu thay đổi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="js/bootstrap.bundle.min.js"></script>
    <script>
    // Đệ qui build <option> phân cấp
    function buildOptions(categories, parentId = null, level = 0) {
        let str = '';
        categories
            .filter(c => String(c.parent_id) === String(parentId))
            .forEach(c => {
                const indent = '\u00A0'.repeat(level * 4);
                str += `<option value="${c.id}">${indent}${c.name}</option>`;
                str += buildOptions(categories, c.id, level + 1);
            });
        return str;
    }

    document.addEventListener('DOMContentLoaded', () => {
        const sel = document.getElementById("modalCategories");
        if (!sel) return;

        // Fetch categories từ API và render vào <select>
        fetch("/CoursePro1/api/category_api.php?tree=0")
            .then(res => res.json())
            .then(json => {
                sel.innerHTML = buildOptions(json.data);
            })
            .catch(err => console.error('Lỗi tải danh mục:', err));

        // Reset chọn khi mở modal Thêm mới
        document.querySelector('button[data-bs-target="#courseModal"]').addEventListener('click', () => {
            sel.querySelectorAll('option').forEach(opt => opt.selected = false);
        });

        // Xử lý xem trước ảnh
        document.getElementById('modalCourseImage').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const preview = document.getElementById('modalImagePreview');
            if (file) {
                const reader = new FileReader();
                reader.onload = evt => {
                    preview.src = evt.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                preview.src = '#';
                preview.style.display = 'none';
            }
        });

        // Xử lý nút Sửa / Xóa (giữ nguyên code bạn đã có)
        // ...
    });
    </script>
</body>

</html>
