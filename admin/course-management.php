<?php
// File: admin/course-management.php
session_start();

// Nếu cần kiểm tra quyền admin:
// if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
//     header('Location: /signin.php');
//     exit;
// }

// Base URL cho API
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http');

$host = $_SERVER['HTTP_HOST'];

$script_path = $_SERVER['SCRIPT_NAME'];

$path_parts = explode('/', ltrim($script_path, '/'));

$app_root_directory_name = $path_parts[0];

$app_root_path_relative = '/' . $app_root_directory_name;

$known_app_subdir_markers = ['/admin/', '/api/', '/includes/'];

$found_marker = false;
foreach ($known_app_subdir_markers as $marker) {
    $pos = strpos($script_path, $marker);
    if ($pos !== false) {
        $app_root_path_relative = substr($script_path, 0, $pos);
        $found_marker = true;
        break;
    }
}

if (!$found_marker) {
    $app_root_path_relative = dirname($script_path);
    if ($app_root_path_relative === '/' && $script_path !== '/') {
        $app_root_path_relative = '';
    } elseif ($app_root_path_relative === '/' && $script_path === '/') {
        $app_root_path_relative = '';
    }
}

if ($app_root_path_relative !== '/' && $app_root_path_relative !== '' && substr($app_root_path_relative, -1) === '/') {
    $app_root_path_relative = rtrim($app_root_path_relative, '/');
}

define('API_BASE', $protocol . '://' . $host . $app_root_path_relative . '/api');

function callApi(string $endpoint, string $method = 'GET', array $payload = []): array
{
    $url = API_BASE . '/' . ltrim($endpoint, '/');
    $options = [
        'http' => [
            'method'        => $method,
            'header'        => "Content-Type: application/json; charset=utf-8",
            'ignore_errors' => true,
        ]
    ];
    if (in_array($method, ['POST', 'PUT', 'DELETE'])) {
        $options['http']['content'] = json_encode($payload);
    }
    $context  = stream_context_create($options);
    $response = @file_get_contents($url, false, $context);
    $result   = json_decode($response, true);
    return is_array($result)
        ? $result
        : ['success' => false, 'message' => 'Invalid API response', 'data' => null];
}

// Lấy danh sách khóa học
$courseResp = callApi('course_api.php', 'GET');
$courses    = $courseResp['success'] ? $courseResp['data'] : [];

// Lấy danh sách danh mục
$catResp   = callApi('category_api.php', 'GET');
$categories = $catResp['success'] ? $catResp['data'] : [];

$instructorResp = callApi('instructor_api.php', 'GET');
$instructors = $instructorResp['success'] ? $instructorResp['data'] : [];

// Đảm bảo session được khởi động ở đầu script, trước bất kỳ output nào.
// Nếu file này đã có session_start() ở đầu rồi thì không cần dòng này nữa.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- Phần hiển thị thông báo debug---

// Hiển thị thông báo thành công (nếu có)
if (!empty($_SESSION['success'])) {
    echo '<div class="alert alert-success" style="background-color: #d4edda; color: #155724; padding: 10px; border: 1px solid #c3e6cb; margin-bottom: 15px; border-radius: 5px;">';
    echo '<h4>Thành công:</h4>';
    echo '<p style="margin: 5px 0;">' . htmlspecialchars($_SESSION['success']) . '</p>';
    echo '</div>';
    unset($_SESSION['success']); // Xóa thông báo sau khi hiển thị
}

// Hiển thị thông báo lỗi (nếu có)
if (!empty($_SESSION['error'])) {
    echo '<div class="alert alert-danger" style="background-color: #f8d7da; color: #721c24; padding: 10px; border: 1px solid #f5c6cb; margin-bottom: 15px; border-radius: 5px;">';
    echo '<h4>Lỗi:</h4>';
    echo '<p style="margin: 5px 0;">' . htmlspecialchars($_SESSION['error']) . '</p>';
    echo '</div>';
    unset($_SESSION['error']); // Xóa thông báo lỗi sau khi hiển thị
}

// Hiển thị thông báo cảnh báo (ví dụ: lỗi upload ảnh không nghiêm trọng)
if (!empty($_SESSION['warning_message'])) {
    echo '<div class="alert alert-warning" style="background-color: #fff3cd; color: #856404; padding: 10px; border: 1px solid #ffeeba; margin-bottom: 15px; border-radius: 5px;">';
    echo '<h4>Cảnh báo:</h4>';
    echo '<p style="margin: 5px 0;">' . htmlspecialchars($_SESSION['warning_message']) . '</p>';
    echo '</div>';
    unset($_SESSION['warning_message']); // Xóa cảnh báo sau khi hiển thị
}


// --- Phần hiển thị thông tin debug ---

// Hiển thị các thông điệp debug chi tiết (nếu có)
if (!empty($_SESSION['debug_messages']) && is_array($_SESSION['debug_messages'])) {
    echo '<div class="alert alert-info" style="background-color: #d1ecf1; color: #0c5460; padding: 10px; border: 1px solid #bee5eb; margin-bottom: 15px; border-radius: 5px;">';
    echo '<h4>Thông tin Debug (c_course.php):</h4>';
    // Sử dụng thẻ <pre> để giữ nguyên định dạng và xuống dòng, thêm cuộn nếu quá dài
    echo '<pre style="white-space: pre-wrap; word-wrap: break-word; max-height: 400px; overflow-y: auto; border: 1px solid #ccc; padding: 5px; background-color: #f9f9f9;">';
    foreach ($_SESSION['debug_messages'] as $index => $message) {
        // Sử dụng htmlspecialchars để tránh XSS nếu thông điệp debug chứa HTML/JS
        echo '<strong>' . ($index + 1) . ':</strong> ' . htmlspecialchars($message) . "\n";
    }
    echo '</pre>';
    echo '</div>';
    // Tùy chọn: Xóa các thông điệp debug sau khi hiển thị nếu bạn chỉ muốn xem chúng một lần
    // unset($_SESSION['debug_messages']);
}

// Phần còn lại của trang course-management.php của bạn...

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
            margin-right: .25rem;
        }

        .action-buttons .btn:last-child {
            margin-right: 0;
        }

        .modal-dialog-scrollable .modal-body {
            max-height: calc(100vh - 260px);
            overflow-y: auto;
        }
    </style>
</head>

<body>
    <?php include __DIR__ . '/template/dashboard.php'; ?>
    <div class="main-content">
        <div class="container-fluid py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="mb-0">Quản lý Khóa học</h3>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#courseModal">
                    <i class="bi bi-plus-lg me-1"></i> Thêm Khóa học
                </button>
            </div>
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">Thao tác thành công!</div>
            <?php endif; ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Tiêu đề</th>
                            <th>Giá (₫)</th>
                            <th>Giảng viên</th>
                            <th>Danh mục</th>
                            <th>Ngày tạo</th>
                            <th class="text-end">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($courses)): ?>
                            <?php foreach ($courses as $i => $c): ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td><?= htmlspecialchars($c['Title'] ?? $c['title'] ?? '') ?></td>
                                    <td><?= number_format($c['Price'], 0, ',', '.') ?> ₫</td>
                                    <td><?= htmlspecialchars($c['CreatedBy'] ?? '') ?></td>
                                    <td>
                                        <?php
                                        $cats = $c['Categories'] ?? [];
                                        $names = array_map(fn($id) => (
                                            array_values(array_filter($categories, fn($cat) => $cat['CategoryID'] === $id))[0]['Name'] ?? ''
                                        ), $cats);
                                        echo htmlspecialchars(implode(', ', $names));
                                        ?>
                                    </td>
                                    <td><?= date('d/m/Y', strtotime($c['CreatedAt'] ?? $c['CreatedDate'] ?? '')) ?></td>
                                    <td class="text-end action-buttons">
                                        <button class="btn btn-sm btn-outline-primary edit-course"
                                            data-course='<?= json_encode($c, JSON_UNESCAPED_UNICODE) ?>'
                                            data-bs-toggle="modal" data-bs-target="#courseModal" title="Sửa">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger delete-course" data-id="<?= $c['CourseID'] ?>" title="Xóa">
                                            <i class="bi bi-trash3-fill"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center">Chưa có khóa học nào.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!-- Modal Thêm / Sửa Khóa học -->
    <div class="modal fade" id="courseModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <form id="courseForm" method="POST" action="../controller/c_course.php" enctype="multipart/form-data">
                    <input type="hidden" name="act" id="formAct" value="create">
                    <input type="hidden" name="CourseID" id="modalCourseID">
                    <div class="modal-header">
                        <h5 class="modal-title" id="courseModalLabel">Thêm Khóa học</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label">Tiêu đề <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="Title" id="modalTitle" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Giá (₫) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="Price" id="modalPrice" step="1000" min="0" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Giảng viên <span class="text-danger">*</span></label>
                                <select class="form-select" name="Instructors" id="modalInstructors" multiple required>
                                    <?php foreach ($instructors as $instructor): ?>
                                        <option value="<?= $instructor['instructorID'] ?>"><?=
                                                                                            $instructor['name'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Danh mục <span class="text-danger">*</span></label>
                                <select class="form-select" name="Categories[]" id="modalCategories" multiple required>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= $cat['id'] ?>"><?=
                                                                            $cat['name'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="form-text text-muted">Giữ Ctrl/Cmd để chọn nhiều.</small>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Mô tả chi tiết</label>
                                <textarea class="form-control" name="Description" id="modalDescription" rows="4"></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Ảnh đại diện</label>
                                <input type="file" class="form-control" name="CourseImage" id="modalCourseImage" accept="image/*">
                                <img id="modalImagePreview" class="mt-2 img-fluid rounded" style="max-height:150px;display:none;">
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
        // JavaScript giống c_course controller đã refactor
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('courseForm');
            const actInput = document.getElementById('formAct');
            const titleIn = document.getElementById('modalTitle');
            const priceIn = document.getElementById('modalPrice');
            const instrIn = document.getElementById('modalInstructor');
            const catsSelect = document.getElementById('modalCategories');
            const descIn = document.getElementById('modalDescription');
            const idIn = document.getElementById('modalCourseID');
            const imgIn = document.getElementById('modalCourseImage');
            const imgPrev = document.getElementById('modalImagePreview');

            imgIn.addEventListener('change', e => {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = ev => {
                        imgPrev.src = ev.target.result;
                        imgPrev.style.display = 'block';
                    };
                    reader.readAsDataURL(file);
                } else {
                    imgPrev.style.display = 'none';
                }
            });

            document.querySelectorAll('.edit-course').forEach(btn => {
                btn.addEventListener('click', () => {
                    const data = JSON.parse(btn.getAttribute('data-course'));
                    actInput.value = 'update';
                    idIn.value = data.CourseID;
                    titleIn.value = data.Title;
                    priceIn.value = data.Price;
                    instrIn.value = data.CreatedBy;
                    descIn.value = data.Description;
                    Array.from(catsSelect.options).forEach(opt => opt.selected = data.Categories.includes(opt.value));
                    imgPrev.style.display = 'none';
                    document.getElementById('courseModalLabel').textContent = 'Sửa Khóa học';
                });
            });

            document.querySelector('button[data-bs-target="#courseModal"]').addEventListener('click', () => {
                actInput.value = 'create';
                form.reset();
                imgPrev.style.display = 'none';
                document.getElementById('courseModalLabel').textContent = 'Thêm Khóa học';
            });

            document.querySelectorAll('.delete-course').forEach(btn => {
                btn.addEventListener('click', () => {
                    if (!confirm('Bạn có chắc muốn xóa khóa học này?')) return;
                    window.location.href = `../controller/c_course.php?act=delete&CourseID=${btn.dataset.id}`;
                });
            });
        });
    </script>
</body>

</html>