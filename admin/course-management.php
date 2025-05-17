<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

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
    $methodUpper = strtoupper($method);

    if ($methodUpper === 'GET' && !empty($payload)) {
        $url .= '?' . http_build_query($payload);
    }

    $headers = "Content-Type: application/json; charset=utf-8\r\n" .
        "Accept: application/json\r\n";
    $token = $_SESSION['user']['token'] ?? null;
    if ($token) {
        $headers .= "Authorization: Bearer " . $token . "\r\n";
    }

    $options = [
        'http' => [
            'method'        => $methodUpper,
            'header'        => $headers,
            'ignore_errors' => true,
        ]
    ];

    if ($methodUpper !== 'GET') {
        if (!empty($payload)) {
            $options['http']['content'] = json_encode($payload);
        } else if (in_array($methodUpper, ['POST', 'PUT'])) {
            $options['http']['content'] = '{}';
        }
    }

    $context  = stream_context_create($options);
    $response = @file_get_contents($url, false, $context);
    $result   = json_decode($response, true);

    $status_code = 500;
    if (isset($http_response_header[0])) {
        preg_match('{HTTP\/\S*\s(\d{3})}', $http_response_header[0], $match);
        if (isset($match[1])) {
            $status_code = intval($match[1]);
        }
    }

    if (!is_array($result)) {
        return [
            'success' => false,
            'message' => 'Invalid API response or failed to decode JSON.',
            'data' => null,
            'raw_response' => $response,
            'http_status_code' => $status_code
        ];
    }

    $result['http_status_code'] = $status_code;
    if (!isset($result['success'])) {
        $result['success'] = ($status_code >= 200 && $status_code < 300);
    }
    return $result;
}

function truncateCreatorId(?string $id, int $prefixLength = 4, int $suffixLength = 2): string
{
    if (empty($id)) {
        return 'N/A';
    }
    $length = strlen($id);
    if ($length <= ($prefixLength + $suffixLength + 3)) {
        return $id;
    }
    return substr($id, 0, $prefixLength) . "..." . substr($id, $length - $suffixLength);
}

// Lấy tất cả danh mục cho dropdown bộ lọc và modal
$catResp    = callApi('category_api.php', 'GET');
$all_categories = $catResp['success'] ? $catResp['data'] : []; // Đổi tên biến để rõ ràng

// Lấy tất cả giảng viên cho modal
$instructorResp = callApi('instructor_api.php', 'GET');
$instructors = $instructorResp['success'] ? $instructorResp['data'] : [];

// Lấy tham số tìm kiếm
$searchTerm = $_GET['search_term'] ?? null;
$searchCategory = $_GET['search_category'] ?? null;

$apiCourseParams = [];
if (!empty($searchTerm)) {
    $apiCourseParams['search_term'] = $searchTerm;
}
if (!empty($searchCategory)) {
    $apiCourseParams['category_id'] = $searchCategory; // Giả sử API của bạn dùng 'category_id'
}

// Gọi API để lấy danh sách khóa học (có thể đã lọc)
$courseResp = callApi('course_api.php', 'GET', $apiCourseParams);
$courses    = $courseResp['success'] ? $courseResp['data'] : [];

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

        .table .column-creator-id {
            max-width: 150px; 
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .table .column-category {
            max-width: 200px; 
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .table .column-header-nowrap {
            white-space: nowrap;
        }
        .filter-form .form-label {
            font-weight: 500;
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

            <form method="GET" action="course-management.php" class="mb-4 p-3 border rounded bg-light filter-form">
                <div class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label for="searchTerm" class="form-label">Tìm theo tên khóa học</label>
                        <input type="text" class="form-control" id="searchTerm" name="search_term" value="<?= htmlspecialchars($searchTerm ?? '') ?>" placeholder="Nhập tên khóa học...">
                    </div>
                    <div class="col-md-4">
                        <label for="searchCategory" class="form-label">Danh mục</label>
                        <select class="form-select" id="searchCategory" name="search_category">
                            <option value="">Tất cả danh mục</option>
                            <?php if (!empty($all_categories)): ?>
                                <?php foreach ($all_categories as $cat): ?>
                                    <option value="<?= htmlspecialchars($cat['id']) ?>" <?= ($searchCategory == $cat['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-auto">
                        <button type="submit" class="btn btn-info w-100"><i class="bi bi-filter me-1"></i> Lọc</button>
                    </div>
                     <div class="col-md-auto">
                        <a href="course-management.php" class="btn btn-outline-secondary w-100"><i class="bi bi-arrow-clockwise me-1"></i> Reset</a>
                    </div>
                </div>
            </form>
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">Thao tác thành công!</div>
            <?php endif; ?>
            <?php if (isset($_GET['error'])): // Hiển thị lỗi nếu có ?>
                <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
            <?php endif; ?>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>STT</th>
                            <th class="column-header-nowrap">Tiêu đề</th>
                            <th>Giá (₫)</th>
                            <th>Giảng viên</th>
                            <th class="column-category column-header-nowrap">Danh mục</th>
                            <th class="column-creator-id column-header-nowrap">Người tạo</th>
                            <th class="text-end column-header-nowrap">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($courses)): ?>
                        <?php foreach ($courses as $i => $c): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><?= htmlspecialchars($c['title'] ?? '') ?></td>
                                <td><?= number_format($c['price'] ?? 0, 0, ',', '.') ?> ₫</td>
                                <td>
                                    <?php
                                    $instructorNames = [];
                                    if (!empty($c['instructors']) && is_array($c['instructors'])) {
                                        foreach ($c['instructors'] as $instructor) {
                                            $firstName = $instructor['firstName'] ?? '';
                                            $lastName = $instructor['lastName'] ?? '';
                                            if (!empty(trim($firstName . $lastName))) {
                                                $instructorNames[] = htmlspecialchars(trim($firstName . " " . $lastName));
                                            }
                                        }
                                    }
                                    echo !empty($instructorNames) ? implode(', ', $instructorNames) : 'N/A';
                                    ?>
                                </td>
                                <td class="column-category">
                                    <?php
                                    $categoryNames = [];
                                    if (!empty($c['categories']) && is_array($c['categories'])) {
                                        foreach ($c['categories'] as $category) {
                                            if (isset($category['categoryName'])) {
                                                $categoryNames[] = htmlspecialchars($category['categoryName']);
                                            }
                                        }
                                    }
                                    echo !empty($categoryNames) ? implode(', ', $categoryNames) : 'N/A';
                                    ?>
                                </td>
                                <td class="column-creator-id"><?= htmlspecialchars(truncateCreatorId($c['createdBy'] ?? null, 4, 2)) ?></td>
                                <td class="text-end action-buttons">
                                    <button class="btn btn-sm btn-outline-primary edit-course"
                                            data-course='<?= htmlspecialchars(json_encode($c, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?>'
                                            data-bs-toggle="modal" data-bs-target="#courseModal" title="Sửa">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger delete-course" data-id="<?= htmlspecialchars($c['courseID'] ?? '') ?>" title="Xóa">
                                        <i class="bi bi-trash3-fill"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">
                                <?php if (!empty($searchTerm) || !empty($searchCategory)): ?>
                                    Không tìm thấy khóa học nào phù hợp với tiêu chí tìm kiếm.
                                <?php else: ?>
                                    Chưa có khóa học nào.
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="modal fade" id="courseModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <form id="courseForm" method="POST" action="../controller/c_course_management.php" enctype="multipart/form-data">
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
                                <select class="form-select" name="Instructors[]" id="modalInstructors" multiple required>
                                    <?php if(!empty($instructors)): ?>
                                        <?php foreach ($instructors as $instructor): ?>
                                            <option value="<?= htmlspecialchars($instructor['instructorID']) ?>"><?= htmlspecialchars($instructor['firstName'] . " " . $instructor['lastName']) ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Danh mục <span class="text-danger">*</span></label>
                                <select class="form-select" name="Categories[]" id="modalCategories" multiple required>
                                     <?php if (!empty($all_categories)): ?>
                                        <?php foreach ($all_categories as $cat): ?>
                                            <option value="<?= htmlspecialchars($cat['id']) ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
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
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.getElementById('courseForm');
        const actInput = document.getElementById('formAct');
        const titleIn = document.getElementById('modalTitle');
        const priceIn = document.getElementById('modalPrice');
        const instructorsSelect = document.getElementById('modalInstructors');
        const categoriesSelect = document.getElementById('modalCategories');
        const descIn = document.getElementById('modalDescription');
        const idIn = document.getElementById('modalCourseID');
        const imgIn = document.getElementById('modalCourseImage');
        const imgPrev = document.getElementById('modalImagePreview');
        const courseModalLabel = document.getElementById('courseModalLabel');

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
                imgPrev.src = '';
                imgPrev.style.display = 'none';
            }
        });

        document.querySelectorAll('.edit-course').forEach(btn => {
            btn.addEventListener('click', () => {
                const dataString = btn.getAttribute('data-course');
                if (!dataString) {
                    console.error('data-course attribute is missing or empty');
                    return;
                }
                try {
                    const data = JSON.parse(dataString);

                    actInput.value = 'update';
                    idIn.value = data.courseID || '';
                    titleIn.value = data.title || '';
                    priceIn.value = data.price || '';
                    descIn.value = data.description || '';

                    if (data.instructors && Array.isArray(data.instructors)) {
                        const selectedInstructorIDs = data.instructors.map(instr => String(instr.instructorID));
                        Array.from(instructorsSelect.options).forEach(option => {
                            option.selected = selectedInstructorIDs.includes(option.value);
                        });
                    } else {
                        Array.from(instructorsSelect.options).forEach(option => option.selected = false);
                    }

                    if (data.categories && Array.isArray(data.categories)) {
                        const selectedCategoryIDs = data.categories.map(cat => String(cat.categoryID));
                        Array.from(categoriesSelect.options).forEach(option => {
                            option.selected = selectedCategoryIDs.includes(option.value);
                        });
                    } else {
                        Array.from(categoriesSelect.options).forEach(option => option.selected = false);
                    }

                    if (data.courseImage) {
                        imgPrev.src = data.courseImage;
                        imgPrev.style.display = 'block';
                    } else {
                        imgPrev.src = '';
                        imgPrev.style.display = 'none';
                    }
                    imgIn.value = '';
                    courseModalLabel.textContent = 'Sửa Khóa học';

                } catch (e) {
                    console.error('Error parsing data-course JSON:', e);
                    console.error('Problematic JSON string:', dataString);
                }
            });
        });

        const addCourseButton = document.querySelector('button[data-bs-target="#courseModal"]');
        if (addCourseButton) {
            addCourseButton.addEventListener('click', () => {
                actInput.value = 'create';
                if(form) form.reset(); // Kiểm tra form có tồn tại không
                Array.from(instructorsSelect.options).forEach(option => option.selected = false);
                Array.from(categoriesSelect.options).forEach(option => option.selected = false);
                imgPrev.src = '';
                imgPrev.style.display = 'none';
                imgIn.value = '';
                idIn.value = '';
                courseModalLabel.textContent = 'Thêm Khóa học';
            });
        }

        document.querySelectorAll('.delete-course').forEach(btn => {
            btn.addEventListener('click', () => {
                if (!confirm('Bạn có chắc muốn xóa khóa học này?')) return;
                const courseIdToDelete = btn.getAttribute('data-id');
                if (courseIdToDelete) {
                    // Tạo một form ẩn để gửi yêu cầu DELETE bằng phương thức POST
                    const deleteForm = document.createElement('form');
                    deleteForm.method = 'POST';
                    deleteForm.action = '../controller/c_course_management.php'; // Action tới controller

                    const actField = document.createElement('input');
                    actField.type = 'hidden';
                    actField.name = 'act';
                    actField.value = 'delete';
                    deleteForm.appendChild(actField);

                    const idField = document.createElement('input');
                    idField.type = 'hidden';
                    idField.name = 'CourseID'; // Tên trường này phải khớp với controller
                    idField.value = courseIdToDelete;
                    deleteForm.appendChild(idField);

                    document.body.appendChild(deleteForm);
                    deleteForm.submit();

                } else {
                    console.error('Không tìm thấy courseID để xóa.');
                    alert('Lỗi: Không tìm thấy ID khóa học để xóa.');
                }
            });
        });
    });
    </script>
</body>
</html>
