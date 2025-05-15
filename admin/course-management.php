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
    $methodUpper = strtoupper($method); // Chuyển method thành chữ hoa để xử lý nhất quán

    // Nếu là GET và có $payload, xây dựng query string
    if ($methodUpper === 'GET' && !empty($payload)) {
        $url .= '?' . http_build_query($payload);
    }

    // Khởi tạo chuỗi header
    $headers = "Content-Type: application/json; charset=utf-8\r\n" .
        "Accept: application/json\r\n";

    // Lấy token từ session nếu có
    $token = $_SESSION['user']['token'] ?? null;

    // Nếu có token, thêm header Authorization
    if ($token) {
        $headers .= "Authorization: Bearer " . $token . "\r\n";
    }

    $options = [
        'http' => [
            'method'        => $methodUpper,
            'header'        => $headers, // Sử dụng chuỗi headers đã được cập nhật
            'ignore_errors' => true,
        ]
    ];

    // Chỉ thêm 'content' (body) cho các method không phải GET và có $payload
    if ($methodUpper !== 'GET') {
        if (!empty($payload)) {
            $options['http']['content'] = json_encode($payload);
        } else if (in_array($methodUpper, ['POST', 'PUT'])) {
            // Gửi một đối tượng JSON rỗng nếu không có payload cho POST/PUT
            $options['http']['content'] = '{}';
        }
    }

    $context  = stream_context_create($options);
    $response = @file_get_contents($url, false, $context);
    $result   = json_decode($response, true);

    $status_code = 500; // Mặc định là lỗi server nếu không lấy được header
    if (isset($http_response_header[0])) {
        preg_match('{HTTP\/\S*\s(\d{3})}', $http_response_header[0], $match);
        if (isset($match[1])) {
            $status_code = intval($match[1]);
        }
    }

    // Nếu $result không phải là mảng (ví dụ: lỗi decode JSON), trả về cấu trúc lỗi chuẩn
    if (!is_array($result)) {
        return [
            'success' => false,
            'message' => 'Invalid API response or failed to decode JSON.',
            'data' => null,
            'raw_response' => $response, // Giữ lại raw response để debug
            'http_status_code' => $status_code
        ];
    }

    // Đảm bảo có 'http_status_code' và 'success' trong kết quả trả về
    $result['http_status_code'] = $status_code;
    if (!isset($result['success'])) {
        $result['success'] = ($status_code >= 200 && $status_code < 300);
    }
    return $result;
}
$courseResp = callApi('course_api.php', 'GET');
$courses    = $courseResp['success'] ? $courseResp['data'] : [];

$catResp   = callApi('category_api.php', 'GET');
$categories = $catResp['success'] ? $catResp['data'] : [];

$instructorResp = callApi('instructor_api.php', 'GET');
$instructors = $instructorResp['success'] ? $instructorResp['data'] : [];

//print_r($instructors);
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
                            <th>Người tạo</th>
                            <th class="text-end">Hành động</th>
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
                                            // Chỉ thêm vào mảng nếu có ít nhất một trong hai tên
                                            if (!empty(trim($firstName . $lastName))) {
                                                $instructorNames[] = htmlspecialchars(trim($firstName . " " . $lastName));
                                            }
                                        }
                                    }
                                    if (!empty($instructorNames)) {
                                        echo implode(', ', $instructorNames);
                                    } else {
                                        echo 'N/A';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    $categoryNames = [];
                                    // Kiểm tra xem $c['categories'] có phải là mảng và không rỗng không
                                    if (!empty($c['categories']) && is_array($c['categories'])) {
                                        foreach ($c['categories'] as $category) {
                                            // Kiểm tra xem mỗi phần tử có 'categoryName' không
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
                                <input type="text" class="form-control" name="Title" id="modalTitle" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Giá (₫) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="Price" id="modalPrice" step="1000" min="0" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Giảng viên <span class="text-danger">*</span></label>
                                <select class="form-select" name="Instructors[]" id="modalInstructors" multiple required>
                                    <?php foreach ($instructors as $instructor): ?>
                                        <option value="<?= $instructor['instructorID'] ?>"><?= $instructor['firstName'] . " " . $instructor['lastName'] ?></option>
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