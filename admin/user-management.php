<?php
// (Tạm thời để trống phần PHP, bạn sẽ cần thêm logic để lấy danh sách người dùng, xử lý form, v.v.)
// Ví dụ dữ liệu mẫu (bạn sẽ thay thế bằng dữ liệu từ database)
$exampleUsers = [
    ['UserID' => 1, 'FullName' => 'Nguyễn Văn Admin', 'Email' => 'admin@example.com', 'Role' => 'Admin', 'JoinDate' => '2024-01-10', 'Status' => 'Active'],
    ['UserID' => 2, 'FullName' => 'Trần Thị Học Viên', 'Email' => 'hocvien@example.com', 'Role' => 'User', 'JoinDate' => '2024-03-15', 'Status' => 'Active'],
    ['UserID' => 3, 'FullName' => 'Lê Văn Instructor', 'Email' => 'instructor@example.com', 'Role' => 'Instructor', 'JoinDate' => '2024-02-01', 'Status' => 'Inactive'],
    ['UserID' => 4, 'FullName' => 'Phạm Thị B', 'Email' => 'phamthib@example.com', 'Role' => 'User', 'JoinDate' => '2025-05-01', 'Status' => 'Pending'],
];
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <title>Quản lý Người dùng</title>
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
        .status-badge {
            font-size: 0.8em;
            padding: 0.3em 0.6em;
        }
    </style>
</head>

<body>
    <?php include('template/dashboard.php'); ?>

    <div class="main-content">
        <div class="container-fluid py-4">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="mb-0">Quản lý Người dùng</h3>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#userModal">
                    <i class="bi bi-person-plus-fill me-1"></i> Thêm người dùng
                </button>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <form class="row g-3 align-items-center">
                        <div class="col-md-4">
                            <label for="filterName" class="visually-hidden">Tên hoặc Email</label>
                            <input type="text" class="form-control" id="filterName" placeholder="Tìm theo tên hoặc email...">
                        </div>
                        <div class="col-md-3">
                            <label for="filterRole" class="visually-hidden">Vai trò</label>
                            <select class="form-select" id="filterRole">
                                <option selected value="">Tất cả vai trò</option>
                                <option value="Admin">Admin</option>
                                <option value="Instructor">Giảng viên</option>
                                <option value="User">Học viên</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                             <label for="filterStatus" class="visually-hidden">Trạng thái</label>
                            <select class="form-select" id="filterStatus">
                                <option selected value="">Tất cả trạng thái</option>
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                                <option value="Pending">Pending</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-outline-secondary w-100">
                                <i class="bi bi-funnel-fill me-1"></i> Lọc
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Họ và Tên</th>
                            <th>Email</th>
                            <th>Vai trò</th>
                            <th>Ngày tham gia</th>
                            <th>Trạng thái</th>
                            <th class="text-end">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (!empty($exampleUsers)) {
                            foreach ($exampleUsers as $i => $user) {
                                $statusClass = '';
                                switch (strtolower($user['Status'])) {
                                    case 'active': $statusClass = 'bg-success'; break;
                                    case 'inactive': $statusClass = 'bg-secondary'; break;
                                    case 'pending': $statusClass = 'bg-warning text-dark'; break;
                                    default: $statusClass = 'bg-light text-dark'; break;
                                }
                                echo "<tr>";
                                echo "<td>".($i+1)."</td>";
                                echo "<td>" . htmlspecialchars($user['FullName']) . "</td>";
                                echo "<td>" . htmlspecialchars($user['Email']) . "</td>";
                                echo "<td>" . htmlspecialchars($user['Role']) . "</td>";
                                echo "<td>" . date('d/m/Y', strtotime($user['JoinDate'])) . "</td>";
                                echo "<td><span class='badge rounded-pill status-badge " . $statusClass . "'>" . htmlspecialchars($user['Status']) . "</span></td>";
                                echo "<td class='text-end action-buttons'>
                                      <button class='btn btn-sm btn-outline-primary edit-user' data-id='{$user['UserID']}' data-bs-toggle='modal' data-bs-target='#userModal' title='Sửa'>
                                          <i class='bi bi-pencil-square'></i>
                                      </button>
                                      <button class='btn btn-sm btn-outline-danger delete-user' data-id='{$user['UserID']}' title='Xóa'>
                                          <i class='bi bi-trash3-fill'></i>
                                      </button>
                                    </td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='7' class='text-center'>Không tìm thấy người dùng nào.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <nav aria-label="User pagination" class="mt-4 d-flex justify-content-center">
                <ul class="pagination">
                    <li class="page-item disabled"><a class="page-link" href="#">Trước</a></li>
                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                    <li class="page-item"><a class="page-link" href="#">2</a></li>
                    <li class="page-item"><a class="page-link" href="#">Sau</a></li>
                </ul>
            </nav>

        </div>
    </div>

    <div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <form id="userForm" method="post" action="user_save.php"> <div class="modal-header">
                        <h5 class="modal-title" id="userModalLabel">Thêm Người dùng</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="UserID" id="modalUserID">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="modalFullName" class="form-label">Họ và Tên <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="modalFullName" name="FullName" required>
                            </div>
                            <div class="col-md-6">
                                <label for="modalEmail" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="modalEmail" name="Email" required>
                            </div>

                            <div class="col-md-6">
                                <label for="modalPassword" class="form-label">Mật khẩu</label>
                                <input type="password" class="form-control" id="modalPassword" name="Password">
                                <small class="form-text text-muted" id="passwordHelp">Để trống nếu không muốn thay đổi mật khẩu.</small>
                            </div>
                            <div class="col-md-6">
                                <label for="modalRole" class="form-label">Vai trò <span class="text-danger">*</span></label>
                                <select class="form-select" id="modalRole" name="Role" required>
                                    <option value="User" selected>Học viên (User)</option>
                                    <option value="Instructor">Giảng viên (Instructor)</option>
                                    <option value="Admin">Quản trị viên (Admin)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="modalStatus" class="form-label">Trạng thái <span class="text-danger">*</span></label>
                                <select class="form-select" id="modalStatus" name="Status" required>
                                    <option value="Active" selected>Active</option>
                                    <option value="Inactive">Inactive</option>
                                    <option value="Pending">Pending</option>
                                </select>
                            </div>
                             </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-save-fill me-1"></i> Lưu người dùng</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const userModal = new bootstrap.Modal(document.getElementById('userModal'));
            const userModalLabel = document.getElementById('userModalLabel');
            const userForm = document.getElementById('userForm');
            const modalUserID = document.getElementById('modalUserID');
            const modalFullName = document.getElementById('modalFullName');
            const modalEmail = document.getElementById('modalEmail');
            const modalPassword = document.getElementById('modalPassword');
            const passwordHelpText = document.getElementById('passwordHelp');
            const modalRole = document.getElementById('modalRole');
            const modalStatus = document.getElementById('modalStatus');
            // const modalProfileImage = document.getElementById('modalProfileImage');
            // const modalUserImagePreview = document.getElementById('modalUserImagePreview');

            document.querySelector('button[data-bs-target="#userModal"]').addEventListener('click', function() {
                userModalLabel.textContent = 'Thêm Người dùng mới';
                userForm.reset();
                modalUserID.value = '';
                passwordHelpText.textContent = 'Mật khẩu là bắt buộc khi thêm mới.';
                modalPassword.required = true; // Bắt buộc mật khẩu khi thêm mới
                // modalUserImagePreview.style.display = 'none';
                // modalUserImagePreview.src = '#';
            });

            document.querySelectorAll('.edit-user').forEach(button => {
                button.addEventListener('click', function() {
                    userModalLabel.textContent = 'Chỉnh sửa Người dùng';
                    const userId = this.getAttribute('data-id');
                    modalUserID.value = userId;
                    passwordHelpText.textContent = 'Để trống nếu không muốn thay đổi mật khẩu.';
                    modalPassword.required = false; // Không bắt buộc mật khẩu khi sửa

                    // TODO: AJAX/Fetch để lấy thông tin người dùng dựa trên userId
                    // và điền vào form. Dưới đây là ví dụ dữ liệu cứng:
                    // fetchUserDetails(userId).then(data => {
                    //     modalFullName.value = data.FullName;
                    //     modalEmail.value = data.Email;
                    //     modalRole.value = data.Role;
                    //     modalStatus.value = data.Status;
                    //     // ... (điền các trường khác)
                    // });
                    
                    // Ví dụ dữ liệu cứng (bạn cần thay bằng AJAX)
                    const userToEdit = <?php echo json_encode($exampleUsers); ?>.find(u => u.UserID == userId);
                    if (userToEdit) {
                        modalFullName.value = userToEdit.FullName;
                        modalEmail.value = userToEdit.Email;
                        modalRole.value = userToEdit.Role;
                        modalStatus.value = userToEdit.Status;
                    }
                     userModal.show();
                });
            });

            // if (modalProfileImage) {
            //     modalProfileImage.addEventListener('change', function(event) {
            //         const file = event.target.files[0];
            //         if (file) {
            //             const reader = new FileReader();
            //             reader.onload = function(e) {
            //                 modalUserImagePreview.src = e.target.result;
            //                 modalUserImagePreview.style.display = 'block';
            //             }
            //             reader.readAsDataURL(file);
            //         } else {
            //             modalUserImagePreview.src = '#';
            //             modalUserImagePreview.style.display = 'none';
            //         }
            //     });
            // }

            document.querySelectorAll('.delete-user').forEach(button => {
                button.addEventListener('click', function() {
                    const userId = this.getAttribute('data-id');
                    const userName = this.closest('tr').querySelector('td:nth-child(2)').textContent;
                    if (confirm(`Bạn có chắc chắn muốn xóa người dùng "${userName}" (ID: ${userId}) không?`)) {
                        // TODO: AJAX để xóa người dùng
                        alert(`Đã yêu cầu xóa người dùng ID: ${userId}. (Cần triển khai logic xóa thực tế)`);
                        // this.closest('tr').remove(); // Xóa hàng sau khi thành công
                    }
                });
            });
        });
    </script>
</body>

</html>