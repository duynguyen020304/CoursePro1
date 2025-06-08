<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$userIsAdmin = isset($_SESSION['user']) && isset($_SESSION['user']['roleID']) && $_SESSION['user']['roleID'] === 'admin';

$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http');
$host = $_SERVER['HTTP_HOST'];
$script_path = $_SERVER['SCRIPT_NAME'];

$app_root_path_relative = '';
$path_parts_for_root = explode('/', trim($script_path, '/'));

if (count($path_parts_for_root) > 1) {
    $admin_pos = strpos($script_path, '/admin/');
    if ($admin_pos !== false) {
        $app_root_path_relative = substr($script_path, 0, $admin_pos);
    } else {
        $app_root_path_relative = dirname(dirname($script_path));
        if ($app_root_path_relative === '/' || $app_root_path_relative === '\\') {
            if (count($path_parts_for_root) <= 1 || $path_parts_for_root[0] === basename($script_path)) {
                $app_root_path_relative = '';
            }
        }
    }
}
$app_root_path_relative = rtrim($app_root_path_relative, '/');

define('APP_ROOT_URL_BASE', $protocol . '://' . $host . $app_root_path_relative);
define('API_BASE_FOR_JS', APP_ROOT_URL_BASE . '/api');
define('APP_BASE_PATH_FOR_IMAGES', $app_root_path_relative);

$available_roles = [
    'student' => 'Học viên',
    'instructor' => 'Giảng viên',
];
const USER_IMAGE_SERVING_SCRIPT_NAME = 'c_file_loader.php';

$success_message = $_SESSION['success_message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;
$form_data = $_SESSION['form_data'] ?? [];

unset($_SESSION['success_message']);
unset($_SESSION['error_message']);
unset($_SESSION['form_data']);

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Người dùng</title>
    <link href="css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.css" rel="stylesheet" />
    <link href="css/base_dashboard.css" rel="stylesheet" />
    <link href="css/admin_style.css" rel="stylesheet" />
    <style>
        .profile-image-sm {
            width: 40px;
            height: 40px;
            object-fit: cover;
            border-radius: 50%;
            margin-right: 10px;
        }
        .action-buttons .btn {
            margin-right: 0.25rem;
        }
        .modal-body .form-label {
            font-weight: 500;
        }
        input[readonly].form-control {
            background-color: #e9ecef;
            opacity: 1;
        }
    </style>
</head>
<body>
<?php include __DIR__ . '/template/dashboard.php'; ?>
<div class="main-content">
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="mb-0">Quản lý Người dùng</h3>
            <?php if ($userIsAdmin): ?>
                <button class="btn btn-primary" id="addNewUserBtn" data-bs-toggle="modal" data-bs-target="#userModal">
                    <i class="bi bi-plus-lg me-1"></i> Thêm Người dùng
                </button>
            <?php endif; ?>
        </div>

        <div id="alertPlaceholderPage">
            <?php if ($success_message): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($success_message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            <?php if ($error_message): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= nl2br(htmlspecialchars($error_message)) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Ảnh</th>
                    <th>Họ và Tên</th>
                    <th>Email</th>
                    <th>Vai trò</th>
                    <th>Ngày tạo</th>
                    <th class="text-end">Hành động</th>
                </tr>
                </thead>
                <tbody id="usersTableBody">
                <?php if (!$userIsAdmin): ?>
                    <tr><td colspan="7" class="text-center text-danger">Bạn không có quyền truy cập chức năng này.</td></tr>
                <?php else: ?>
                    <tr><td colspan="7" class="text-center">Đang tải dữ liệu người dùng...</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="userForm" action="../controller/c_user_management.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="userID" id="modalUserID" value="<?= htmlspecialchars($form_data['userID'] ?? '') ?>" />
                <input type="hidden" name="act" id="formAct" value="create" /> <input type="hidden" name="profileImage" id="modalProfileImagePathHidden" value="<?= htmlspecialchars($form_data['profileImage'] ?? '') ?>" />

                <div class="modal-header">
                    <h5 class="modal-title" id="userModalLabel">Thêm Người dùng</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="alertPlaceholderModal"></div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="modalFirstName" class="form-label">Tên <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="modalFirstName" name="firstName" required value="<?= htmlspecialchars($form_data['firstName'] ?? '') ?>" />
                        </div>
                        <div class="col-md-6">
                            <label for="modalLastName" class="form-label">Họ <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="modalLastName" name="lastName" required value="<?= htmlspecialchars($form_data['lastName'] ?? '') ?>" />
                        </div>
                        <div class="col-12">
                            <label for="modalEmail" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="modalEmail" name="email" required value="<?= htmlspecialchars($form_data['email'] ?? '') ?>" />
                        </div>
                        <div class="col-md-6">
                            <label for="modalPassword" class="form-label">Mật khẩu <span id="passwordRequiredSpan" class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="modalPassword" name="password" />
                            <small id="passwordHelp" class="form-text text-muted">Để trống nếu không muốn thay đổi mật khẩu khi cập nhật.</small>
                        </div>
                        <div class="col-md-6">
                            <label for="modalRoleID" class="form-label">Vai trò <span class="text-danger">*</span></label>
                            <select class="form-select" id="modalRoleID" name="roleID" required>
                                <option value="" disabled <?= empty($form_data['roleID']) ? 'selected' : '' ?>>Chọn vai trò...</option>
                                <?php foreach ($available_roles as $role_id => $role_name): ?>
                                    <option value="<?= htmlspecialchars($role_id) ?>" <?= (isset($form_data['roleID']) && $form_data['roleID'] == $role_id) ? 'selected' : '' ?>><?= htmlspecialchars($role_name) ?></option>
                                <?php endforeach; ?>
                                <option value="admin" <?= (isset($form_data['roleID']) && $form_data['roleID'] == 'admin') ? 'selected' : '' ?>>Quản trị viên</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label for="modalProfileImageFile" class="form-label">Ảnh đại diện</label>
                            <input type="file" class="form-control" id="modalProfileImageFile" name="profileImageFile" accept="image/*" />
                            <img id="modalProfileImagePreview" src="#" alt="Xem trước ảnh" class="mt-2 img-fluid rounded" style="max-height:100px; display:none;" />
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary" id="saveUserBtn">
                        <i class="bi bi-save-fill me-1"></i> Lưu
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<form id="deleteUserForm" action="../controller/c_user_management.php" method="POST" style="display: none;">
    <input type="hidden" name="act" value="delete">
    <input type="hidden" name="userID" id="deleteUserID">
</form>

<script src="js/bootstrap.bundle.min.js"></script>
<script>
    const API_BASE_FOR_JS = '<?= API_BASE_FOR_JS ?>';
    const USER_TOKEN = '<?= $_SESSION['user']['token'] ?? '' ?>';
    const APP_IMG_PATH_PREFIX = '<?= rtrim(APP_BASE_PATH_FOR_IMAGES, '/') ?>';
    const USER_IMAGE_HANDLER_SCRIPT = '<?= USER_IMAGE_SERVING_SCRIPT_NAME ?>';
    const PROJECT_BASE_URL = '<?= APP_ROOT_URL_BASE ?>';

    function showAlertJS(message, type = 'success', placeholderId = 'alertPlaceholderPage', duration = 4000) {
        const alertPlaceholder = document.getElementById(placeholderId);
        if (!alertPlaceholder) return;
        const wrapper = document.createElement('div');
        wrapper.innerHTML = [
            `<div class="alert alert-${type} alert-dismissible fade show" role="alert">`,
            `   <div>${message.replace(/\n/g, '<br>')}</div>`,
            '   <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>',
            '</div>'
        ].join('');
        alertPlaceholder.innerHTML = '';
        alertPlaceholder.append(wrapper);
        if (duration) {
            setTimeout(() => {
                const alertDiv = wrapper.querySelector('.alert');
                if (alertDiv) {
                    if (typeof bootstrap !== 'undefined' && bootstrap.Alert && bootstrap.Alert.getInstance(alertDiv)) {
                        new bootstrap.Alert(alertDiv).close();
                    } else if (alertDiv.parentNode) {
                        alertDiv.remove();
                    }
                }
            }, duration);
        }
    }

    async function fetchApiForView(endpoint, method = 'GET', payload = null) {
        let url = `${API_BASE_FOR_JS}/${endpoint}`;
        const options = {
            method: method.toUpperCase(),
            headers: {}
        };
        if (USER_TOKEN) {
            options.headers['Authorization'] = `Bearer ${USER_TOKEN}`;
        }
        if (method.toUpperCase() === 'GET' && payload) {
            url += `?${new URLSearchParams(payload).toString()}`;
        } else if (['POST', 'PUT', 'DELETE'].includes(method.toUpperCase()) && payload) {
            options.headers['Content-Type'] = 'application/json';
            options.body = JSON.stringify(payload);
        }
        try {
            const response = await fetch(url, options);
            let responseData;
            const contentType = response.headers.get("content-type");
            if (response.status === 204) {
                responseData = { success: true, message: "Thao tác thành công."};
            } else if (contentType && contentType.includes("application/json")) {
                responseData = await response.json();
            } else {
                const textResponse = await response.text();
                try {
                    responseData = JSON.parse(textResponse);
                } catch (e) {
                    responseData = { success: response.ok, message: textResponse || "Lỗi không xác định từ API", data: textResponse };
                }
            }
            responseData.http_status_code = response.status;
            if (typeof responseData.success === 'undefined') {
                responseData.success = response.ok;
            }
            return responseData;
        } catch (error) {
            console.error(`Lỗi gọi API JS cho ${method} ${url}:`, error);
            return { success: false, message: `Lỗi phía client (JavaScript): ${error.message}`, http_status_code: 0 };
        }
    }

    function formatDate(dateString) {
        if (!dateString) return 'N/A';
        try {
            const date = new Date(dateString);
            if (isNaN(date.getTime())) return dateString;
            return date.toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric' });
        } catch (e) {
            return dateString;
        }
    }

    function getUserProfileImageURL(profileImagePath, userID) {
        if (!profileImagePath) {
            return `https://placehold.co/40x40/EFEFEF/AAAAAA?text=User&font=Inter`;
        }
        if (USER_IMAGE_HANDLER_SCRIPT && !profileImagePath.startsWith('http')) {
            const imageName = profileImagePath.includes('/') ? profileImagePath.split('/').pop() : profileImagePath;
            const effectiveUserID = userID || (profileImagePath.includes('/users/') ? profileImagePath.split('/users/')[1].split('/')[0] : null);
            if (effectiveUserID) {
                console.log(`${PROJECT_BASE_URL}/controller/${USER_IMAGE_HANDLER_SCRIPT}?act=serve_user_image&user_id=${encodeURIComponent(effectiveUserID)}&image=${encodeURIComponent(imageName)}`)
                return `${PROJECT_BASE_URL}/controller/${USER_IMAGE_HANDLER_SCRIPT}?act=serve_user_image&user_id=${encodeURIComponent(effectiveUserID)}&image=${encodeURIComponent(imageName)}`;
            }
            return `${APP_IMG_PATH_PREFIX}/${profileImagePath}`.replace(/\/\//g, '/');
        }
        if (profileImagePath.startsWith('http')) {
            return profileImagePath;
        }
        return `${APP_IMG_PATH_PREFIX}/${profileImagePath}`.replace(/\/\//g, '/');
    }

    async function loadUsers() {
        const tableBody = document.getElementById('usersTableBody');
        if (!document.getElementById('addNewUserBtn')) {
            if(tableBody) tableBody.innerHTML = '<tr><td colspan="7" class="text-center text-danger">Bạn không có quyền truy cập chức năng này.</td></tr>';
            return;
        }
        if(tableBody) tableBody.innerHTML = '<tr><td colspan="7" class="text-center">Đang tải dữ liệu người dùng... <div class="spinner-border spinner-border-sm ms-2" role="status"><span class="visually-hidden">Loading...</span></div></td></tr>';

        const response = await fetchApiForView('user_api.php');

        if (response.success && Array.isArray(response.data)) {
            if (response.data.length === 0) {
                if(tableBody) tableBody.innerHTML = '<tr><td colspan="7" class="text-center">Không có người dùng nào.</td></tr>';
                return;
            }
            if(tableBody) tableBody.innerHTML = '';
            response.data.forEach(user => {
                const profilePicUrl = getUserProfileImageURL(user.profileImage, user.userID);
                const row = `
                <tr>
                    <td>${user.userID ? htmlspecialchars(user.userID.substring(0, 8)) : 'N/A'}...</td>
                    <td><img src="${profilePicUrl}" alt="${htmlspecialchars(user.firstName || 'User')}" class="profile-image-sm" onerror="this.onerror=null; this.src='https://placehold.co/40x40/EFEFEF/AAAAAA?text=Err&font=Inter';" /></td>
                    <td>${htmlspecialchars(user.firstName || '')} ${htmlspecialchars(user.lastName || '')}</td>
                    <td>${htmlspecialchars(user.email || '')}</td>
                    <td><span class="badge bg-${getRoleBadgeClass(user.roleID)}">${htmlspecialchars(user.roleID || 'N/A')}</span></td>
                    <td>${formatDate(user.created_at)}</td>
                    <td class="text-end action-buttons">
                        <button class="btn btn-sm btn-outline-primary edit-user-btn" data-userid="${user.userID}" title="Sửa Người dùng" data-bs-toggle="modal" data-bs-target="#userModal">
                            <i class="bi bi-pencil-square"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger delete-user-btn" data-userid="${user.userID}" title="Xóa Người dùng">
                            <i class="bi bi-trash3-fill"></i>
                        </button>
                    </td>
                </tr>
            `;
                if(tableBody) tableBody.insertAdjacentHTML('beforeend', row);
            });
            attachActionListenersJS();
        } else {
            if(tableBody) tableBody.innerHTML = `<tr><td colspan="7" class="text-center text-danger">Lỗi tải danh sách người dùng: ${response.message || 'Không rõ lỗi từ API'} (Code: ${response.http_status_code || 'N/A'})</td></tr>`;
        }
    }

    function getRoleBadgeClass(roleID) {
        switch (roleID) {
            case 'admin': return 'danger';
            case 'instructor': return 'info';
            case 'student': return 'success';
            default: return 'secondary';
        }
    }

    function htmlspecialchars(str) {
        if (str === null || typeof str === 'undefined') return '';
        if (typeof str !== 'string') str = String(str);
        const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
        return str.replace(/[&<>"']/g, m => map[m]);
    }

    function resetUserModalForJS() {
        const form = document.getElementById('userForm');
        if(form) form.reset();

        const actEl = document.getElementById('formAct');
        if(actEl) actEl.value = 'create';

        const userIdEl = document.getElementById('modalUserID');
        if(userIdEl) userIdEl.value = '';

        const labelEl = document.getElementById('userModalLabel');
        if(labelEl) labelEl.textContent = 'Thêm Người dùng';

        const emailEl = document.getElementById('modalEmail');
        if(emailEl) {
            emailEl.readOnly = false;
        }

        const passReqSpan = document.getElementById('passwordRequiredSpan');
        if(passReqSpan) passReqSpan.style.display = 'inline';

        const passHelp = document.getElementById('passwordHelp');
        if(passHelp) passHelp.textContent = 'Mật khẩu cho tài khoản mới.';

        const alertModal = document.getElementById('alertPlaceholderModal');
        if(alertModal) alertModal.innerHTML = '';

        const imgPreview = document.getElementById('modalProfileImagePreview');
        if(imgPreview) {
            imgPreview.style.display = 'none';
            imgPreview.src = '#';
        }
        const imgPathHidden = document.getElementById('modalProfileImagePathHidden');
        if(imgPathHidden) imgPathHidden.value = '';
    }

    function setupEditModalJS(userID) {
        resetUserModalForJS();
        const formActEl = document.getElementById('formAct');
        if (formActEl) formActEl.value = 'update';

        const modalLabelEl = document.getElementById('userModalLabel');
        if (modalLabelEl) modalLabelEl.textContent = 'Sửa Người dùng';

        const emailEl = document.getElementById('modalEmail');
        if (emailEl) {
            emailEl.readOnly = true;
        }

        const passReqSpan = document.getElementById('passwordRequiredSpan');
        if (passReqSpan) passReqSpan.style.display = 'none';

        const passHelpEl = document.getElementById('passwordHelp');
        if (passHelpEl) passHelpEl.textContent = 'Để trống nếu không muốn thay đổi mật khẩu.';

        fetchApiForView(`user_api.php?id=${userID}`)
            .then(response => {
                if (response.success && response.data) {
                    const user = response.data;
                    const modalUserIDEl = document.getElementById('modalUserID');
                    if (modalUserIDEl) modalUserIDEl.value = user.userID;

                    const modalFirstNameEl = document.getElementById('modalFirstName');
                    if (modalFirstNameEl) modalFirstNameEl.value = user.firstName || '';

                    const modalLastNameEl = document.getElementById('modalLastName');
                    if (modalLastNameEl) modalLastNameEl.value = user.lastName || '';

                    if (emailEl) emailEl.value = user.email || '';

                    const modalRoleIDEl = document.getElementById('modalRoleID');
                    if (modalRoleIDEl) modalRoleIDEl.value = user.roleID || '';

                    const imgPreview = document.getElementById('modalProfileImagePreview');
                    const existingImagePathHidden = document.getElementById('modalProfileImagePathHidden');
                    if (existingImagePathHidden) existingImagePathHidden.value = user.profileImage || '';

                    if (user.profileImage && imgPreview) {
                        imgPreview.src = getUserProfileImageURL(user.profileImage, user.userID);
                        imgPreview.style.display = 'block';
                    } else if (imgPreview) {
                        imgPreview.style.display = 'none';
                        imgPreview.src = '#';
                    }
                } else {
                    showAlertJS(`Lỗi tải thông tin người dùng: ${response.message || 'Không rõ lỗi'} (Code: ${response.http_status_code || 'N/A'})`, 'danger', 'alertPlaceholderModal');
                }
            })
            .catch(error => {
                showAlertJS(`Lỗi client khi tải thông tin người dùng: ${error.message}`, 'danger', 'alertPlaceholderModal');
            });
    }

    const modalProfileImageFileEl = document.getElementById('modalProfileImageFile');
    if (modalProfileImageFileEl) {
        modalProfileImageFileEl.addEventListener('change', function (event) {
            const file = event.target.files[0];
            const preview = document.getElementById('modalProfileImagePreview');
            const existingPathHidden = document.getElementById('modalProfileImagePathHidden');
            const currentUserID = document.getElementById('modalUserID').value;

            if (file && preview) {
                const reader = new FileReader();
                reader.onload = function (e) { preview.src = e.target.result; preview.style.display = 'block'; };
                reader.readAsDataURL(file);
            } else if (preview) {
                const existingPath = existingPathHidden ? existingPathHidden.value : '';
                if (existingPath && currentUserID) {
                    preview.src = getUserProfileImageURL(existingPath, currentUserID);
                    preview.style.display = 'block';
                } else {
                    preview.src = '#';
                    preview.style.display = 'none';
                }
            }
        });
    }

    function attachActionListenersJS() {
        document.querySelectorAll('.edit-user-btn').forEach(button => {
            const newButton = button.cloneNode(true);
            button.parentNode.replaceChild(newButton, button);
            newButton.addEventListener('click', () => {
                const userID = newButton.dataset.userid;
                if (userID) {
                    setupEditModalJS(userID);
                }
            });
        });
        document.querySelectorAll('.delete-user-btn').forEach(button => {
            const newButton = button.cloneNode(true);
            button.parentNode.replaceChild(newButton, button);
            newButton.addEventListener('click', () => {
                const userID = newButton.dataset.userid;
                if (userID) {
                    if (confirm(`Bạn có chắc muốn xóa người dùng ID: ${htmlspecialchars(userID.substring(0,8))}...?`)) {
                        const deleteForm = document.getElementById('deleteUserForm');
                        const deleteUserIDInput = document.getElementById('deleteUserID');
                        if (deleteForm && deleteUserIDInput) {
                            deleteUserIDInput.value = userID;
                            deleteForm.submit();
                        }
                    }
                }
            });
        });
    }

    const addNewUserBtn = document.getElementById('addNewUserBtn');
    if(addNewUserBtn) {
        addNewUserBtn.addEventListener('click', () => {
            resetUserModalForJS();
        });
    }

    const userModalElement = document.getElementById('userModal');
    if (userModalElement) {
        userModalElement.addEventListener('show.bs.modal', function (event) {
            const relatedTarget = event.relatedTarget;
            if (relatedTarget && relatedTarget.id === 'addNewUserBtn') {
                resetUserModalForJS();
            }
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        const currentUserIsAdmin = <?= json_encode($userIsAdmin) ?>;
        if (currentUserIsAdmin) {
            loadUsers();
        } else {
            const tableBody = document.getElementById('usersTableBody');
            if(tableBody) tableBody.innerHTML = '<tr><td colspan="7" class="text-center text-danger">Bạn không có quyền truy cập chức năng này.</td></tr>';
            const addNewBtn = document.getElementById('addNewUserBtn');
            if(addNewBtn) addNewBtn.style.display = 'none';
        }
        const phpFormData = <?= json_encode($form_data) ?>;
        const phpErrorMessage = <?= json_encode($error_message) ?>;
        if (phpErrorMessage && Object.keys(phpFormData).length > 0 && phpFormData.act) {
            const formActEl = document.getElementById('formAct');
            if (formActEl) formActEl.value = phpFormData.act;
            const modalUserIDEl = document.getElementById('modalUserID');
            if (modalUserIDEl) modalUserIDEl.value = phpFormData.userID || '';
            const modalFirstNameEl = document.getElementById('modalFirstName');
            if (modalFirstNameEl) modalFirstNameEl.value = phpFormData.firstName || '';
            const modalLastNameEl = document.getElementById('modalLastName');
            if (modalLastNameEl) modalLastNameEl.value = phpFormData.lastName || '';
            const modalEmailEl = document.getElementById('modalEmail');
            if (modalEmailEl) modalEmailEl.value = phpFormData.email || '';
            const modalRoleIDEl = document.getElementById('modalRoleID');
            if (modalRoleIDEl) modalRoleIDEl.value = phpFormData.roleID || '';
            const modalProfileImagePathHiddenEl = document.getElementById('modalProfileImagePathHidden');
            if (modalProfileImagePathHiddenEl) modalProfileImagePathHiddenEl.value = phpFormData.profileImage || '';

            const modalLabelEl = document.getElementById('userModalLabel');
            const passwordReqSpanEl = document.getElementById('passwordRequiredSpan');
            const passwordHelpEl = document.getElementById('passwordHelp');

            if (phpFormData.act === 'update') {
                if(modalLabelEl) modalLabelEl.textContent = 'Sửa Người dùng (Vui lòng kiểm tra lại)';
                if(modalEmailEl) modalEmailEl.readOnly = true;
                if(passwordReqSpanEl) passwordReqSpanEl.style.display = 'none';
                if(passwordHelpEl) passwordHelpEl.textContent = 'Để trống nếu không muốn thay đổi mật khẩu.';
                if (phpFormData.profileImage && modalProfileImagePreview) {
                    modalProfileImagePreview.src = getUserProfileImageURL(phpFormData.profileImage, phpFormData.userID);
                    modalProfileImagePreview.style.display = 'block';
                }
            } else {
                if(modalLabelEl) modalLabelEl.textContent = 'Thêm Người dùng (Vui lòng kiểm tra lại)';
                if(modalEmailEl) modalEmailEl.readOnly = false;
                if(passwordReqSpanEl) passwordReqSpanEl.style.display = 'inline';
                if(passwordHelpEl) passwordHelpEl.textContent = 'Mật khẩu cho tài khoản mới.';
            }
            const userModalInstance = new bootstrap.Modal(document.getElementById('userModal'));
            userModalInstance.show();
            if (phpErrorMessage) {
                showAlertJS(phpErrorMessage, 'danger', 'alertPlaceholderModal', null);
            }
        }
    });
</script>
</body>
</html>