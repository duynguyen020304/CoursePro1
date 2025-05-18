<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http');
$host = $_SERVER['HTTP_HOST'];
$script_path = $_SERVER['SCRIPT_NAME'];

$app_root_path_relative = '';
$path_parts = explode('/', trim($script_path, '/'));

if (count($path_parts) > 1) {
    $admin_pos = strpos($script_path, '/admin/');
    if ($admin_pos !== false) {
        $app_root_path_relative = substr($script_path, 0, $admin_pos);
    } else {
        $app_root_path_relative = dirname(dirname($script_path));
        if ($app_root_path_relative === '/' || $app_root_path_relative === '\\') {
            if (count($path_parts) <= 1 || $path_parts[0] === basename($script_path)) {
                $app_root_path_relative = '';
            }
        }
    }
}
$app_root_path_relative = rtrim($app_root_path_relative, '/');

define('APP_ROOT_URL_BASE', $protocol . '://' . $host . $app_root_path_relative);
define('API_BASE', APP_ROOT_URL_BASE . '/api');
define('APP_BASE_PATH_FOR_IMAGES', $app_root_path_relative);

function callApi(string $endpoint, string $method = 'GET', array $payload = []): array
{
    $url = API_BASE . '/' . ltrim($endpoint, '/');
    $methodUpper = strtoupper($method);
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
            'header'        => $headers,
            'ignore_errors' => true,
            'timeout'       => 15,
        ]
    ];

    if ($methodUpper !== 'GET' && $methodUpper !== 'HEAD') {
        if (!empty($payload)) {
            $options['http']['content'] = json_encode($payload);
        } else if (in_array($methodUpper, ['POST', 'PUT', 'PATCH'])) {
            $options['http']['content'] = '{}';
        }
    }

    $context  = stream_context_create($options);
    $response = @file_get_contents($url, false, $context);
    $responseHeaders = $http_response_header ?? [];

    $status_code = null;
    foreach ($responseHeaders as $header) {
        if (preg_match('{HTTP/\d\.\d\s+(\d+)\s+}', $header, $match)) {
            $status_code = intval($match[1]);
            break;
        }
    }

    if ($response === false) {
        return [
            'success' => false,
            'message' => 'API connection failed or timed out.',
            'data' => null,
            'http_status_code' => $status_code ?? 0
        ];
    }

    $decodedResponse = json_decode($response, true);
    $jsonError = json_last_error();

    if ($response === '' || ($decodedResponse === null && $jsonError === JSON_ERROR_NONE && $status_code === 204)) {
        return [
            'success' => true,
            'message' => 'Operation successful with no content.',
            'data' => null,
            'http_status_code' => $status_code
        ];
    }

    if ($decodedResponse === null && $jsonError !== JSON_ERROR_NONE) {
        return [
            'success' => false,
            'message' => 'Invalid API response format (not JSON). Error: ' . json_last_error_msg(),
            'data' => null,
            'raw_response' => $response,
            'raw_response' => $response,
            'http_status_code' => $status_code
        ];
    }

    if (!is_array($decodedResponse)) {
        $decodedResponse = ['data' => $decodedResponse];
    }

    if (!isset($decodedResponse['http_status_code'])) {
        $decodedResponse['http_status_code'] = $status_code;
    }
    if (!isset($decodedResponse['success'])) {
        $decodedResponse['success'] = ($status_code >= 200 && $status_code < 300);
    }
    return $decodedResponse;
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
$instructors = $instructorResp['success'] ? ($instructorResp['data'] ?? []) : [];

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
    <link href="css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <link href="css/base_dashboard.css" rel="stylesheet" />
    <link href="css/admin_style.css" rel="stylesheet" />
    <style>
        .action-buttons .btn {
            margin-right: 0.25rem;
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
            <button class="btn btn-primary add-new-course-btn">
                <i class="bi bi-plus-lg me-1"></i> Thêm Khóa học
            </button>
        </div>

        <div id="alertPlaceholder"></div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
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

                                    if (!empty($categoryNames)) {
                                        echo implode(', ', $categoryNames);
                                    } else {
                                        echo 'N/A';
                                    }
                                    ?>
                                </td>
                                <td><?= htmlspecialchars($c['createdBy'] ?? 'N/A') ?></td>
                                <?php /* Nếu có ngày tạo thực sự, ví dụ $c['createdAt']:
                <td><?= htmlspecialchars(isset($c['createdAt']) ? date("d/m/Y", strtotime($c['createdAt'])) : 'N/A') ?></td>
                */ ?>
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
                                <input type="text" class="form-control" name="Title" id="modalTitle" required />
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Giá (₫) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="Price" id="modalPrice" step="1000" min="0" required />
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Giảng viên <span class="text-danger">*</span></label>
                                <select class="form-select" name="Instructors[]" id="modalInstructors" multiple required>
                                    <?php foreach ($instructors as $instructor): ?>
                                        <option value="<?= $instructor['instructorID'] ?>"><?= $instructor['firstName'] . " " . $instructor['lastName'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="form-text text-muted">Giữ Ctrl/Cmd để chọn nhiều.</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Danh mục <span class="text-danger">*</span></label>
                                <select class="form-select" name="Categories[]" id="modalCategories" multiple required>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= $cat['id'] ?>"><?=
                                                                            $cat['name'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="form-text text-muted">Giữ Ctrl/Cmd để chọn nhiều.</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Mô tả chi tiết</label>
                                <textarea class="form-control" name="Description" id="modalDescription" rows="3"></textarea>
                            </div>
                            <div class="mb-3">
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
        // JavaScript giống c_course controller đã refactor - ĐÃ SỬA LỖI
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('courseForm');
            const actInput = document.getElementById('formAct');
            const titleIn = document.getElementById('modalTitle');
            const priceIn = document.getElementById('modalPrice');
            // Sửa lại cách lấy select cho giảng viên và danh mục
            const instructorsSelect = document.getElementById('modalInstructors'); // Sửa ID
            const categoriesSelect = document.getElementById('modalCategories'); // Giữ nguyên, nhưng sẽ dùng biến này
            const descIn = document.getElementById('modalDescription');
            const idIn = document.getElementById('modalCourseID');
            const imgIn = document.getElementById('modalCourseImage');
            const imgPrev = document.getElementById('modalImagePreview');
            const courseModalLabel = document.getElementById('courseModalLabel'); // Lấy label của modal

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
                    imgPrev.src = ''; // Xóa ảnh preview nếu không có file
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
                        const data = JSON.parse(dataString); // Parse JSON từ data-course

                        actInput.value = 'update';
                        // Sử dụng đúng tên thuộc tính từ JSON (thường là chữ thường)
                        idIn.value = data.courseID || ''; // Đảm bảo có giá trị hoặc là chuỗi rỗng
                        titleIn.value = data.title || '';
                        priceIn.value = data.price || '';
                        descIn.value = data.description || ''; // Sửa: data.description

                        // Xử lý chọn Giảng viên (Instructors)
                        if (data.instructors && Array.isArray(data.instructors)) {
                            const selectedInstructorIDs = data.instructors.map(instr => instr.instructorID);
                            Array.from(instructorsSelect.options).forEach(option => {
                                option.selected = selectedInstructorIDs.includes(option.value);
                            });
                        } else {
                            // Bỏ chọn tất cả nếu không có dữ liệu giảng viên
                            Array.from(instructorsSelect.options).forEach(option => option.selected = false);
                        }

                        // Xử lý chọn Danh mục (Categories)
                        if (data.categories && Array.isArray(data.categories)) {
                            // Lấy mảng các categoryID từ đối tượng data.categories
                            const selectedCategoryIDs = data.categories.map(cat => cat.categoryID);
                            Array.from(categoriesSelect.options).forEach(option => {
                                // So sánh giá trị của option (là ID) với mảng các ID đã chọn
                                option.selected = selectedCategoryIDs.includes(option.value);
                            });
                        } else {
                            // Bỏ chọn tất cả nếu không có dữ liệu danh mục
                            Array.from(categoriesSelect.options).forEach(option => option.selected = false);
                        }

                        // Xử lý ảnh preview (nếu bạn lưu đường dẫn ảnh trong data.courseImage)
                        if (data.courseImage) { // Giả sử bạn có trường courseImage trong JSON
                            imgPrev.src = data.courseImage; // Cần đảm bảo đường dẫn này đúng
                            imgPrev.style.display = 'block';
                        } else {
                            imgPrev.src = '';
                            imgPrev.style.display = 'none';
                        }
                        imgIn.value = ''; // Reset input file

                        courseModalLabel.textContent = 'Sửa Khóa học';

                    } catch (e) {
                        console.error('Error parsing data-course JSON:', e);
                        console.error('Problematic JSON string:', dataString);
                    }
                });
            });

            // Nút "Thêm Khóa học"
            document.querySelector('button[data-bs-target="#courseModal"]').addEventListener('click', () => {
                actInput.value = 'create';
                form.reset(); // Reset toàn bộ form
                // Bỏ chọn tất cả các options trong select multiple
                Array.from(instructorsSelect.options).forEach(option => option.selected = false);
                Array.from(categoriesSelect.options).forEach(option => option.selected = false);

                imgPrev.src = '';
                imgPrev.style.display = 'none';
                imgIn.value = ''; // Đảm bảo input file cũng được reset
                courseModalLabel.textContent = 'Thêm Khóa học';
            });

            document.querySelectorAll('.delete-course').forEach(btn => {
                btn.addEventListener('click', () => {
                    if (!confirm('Bạn có chắc muốn xóa khóa học này?')) return;
                    actInput.value = 'delete';
                    const courseIdToDelete = btn.getAttribute('data-id');
                    window.location.href = `../controller/c_course_management.php?act=delete&courseID=${courseIdToDelete}`;
                });
            });
        });
    </script>
</body>
</html>
