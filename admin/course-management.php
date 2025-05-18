<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Helper function to truncate text and add ellipsis
// Returns the raw string; sanitization (htmlspecialchars) should be done at echo
function truncate_text_with_ellipsis_raw($text, $max_length = 20, $ellipsis = '......') {
    if ($text === null || $text === 'N/A') { // Handle N/A case
        return $text;
    }
    if (mb_strlen($text, 'UTF-8') > $max_length) {
        return mb_substr($text, 0, $max_length, 'UTF-8') . $ellipsis;
    }
    return $text;
}

// Helper function to format creator name
// Returns the formatted raw string; sanitization (htmlspecialchars) should be done at echo
function format_display_creator_raw($creator_name_raw) {
    if ($creator_name_raw === null || $creator_name_raw === 'N/A') {
        return 'N/A';
    }

    // Rule for "admin...NN" (NN are the last 2 characters of the original string)
    // Check if starts with 'admin' (case-insensitive) and is long enough
    if (stripos($creator_name_raw, 'admin') === 0 && mb_strlen($creator_name_raw, 'UTF-8') >= 5 + 2) { // "admin" (5 chars) + last 2 chars
        $last_two = mb_substr($creator_name_raw, -2, null, 'UTF-8');
        return 'admin...' . $last_two;
    }
    
    // Default rule for other names: truncate and add "......"
    // Example: keep the first 15 characters
    return truncate_text_with_ellipsis_raw($creator_name_raw, 15, '......');
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
            'timeout'       => 15,
        ]
    ];

    if ($methodUpper !== 'GET' && $methodUpper !== 'HEAD') {
        if (!empty($payload)) {
            $options['http']['content'] = json_encode($payload);
        } else if (in_array($methodUpper, ['POST', 'PUT', 'PATCH'])) {
            // Send empty JSON object for POST/PUT/PATCH if payload is empty, as some APIs might expect it
            $options['http']['content'] = '{}';
        }
    }

    $context  = stream_context_create($options);
    $response = @file_get_contents($url, false, $context); // Suppress errors to handle them manually
    $responseHeaders = $http_response_header ?? []; // Get headers if available

    // Extract status code from headers
    $status_code = null;
    foreach ($responseHeaders as $header) {
        if (preg_match('{HTTP/\d\.\d\s+(\d+)\s+}', $header, $match)) {
            $status_code = intval($match[1]);
            break;
        }
    }

    if ($response === false) {
        // Error opening stream or other connection issue
        return [
            'success' => false,
            'message' => 'API connection failed or timed out.',
            'data' => null,
            'http_status_code' => $status_code ?? 0 // Use 0 if status code couldn't be determined
        ];
    }

    $decodedResponse = json_decode($response, true);
    $jsonError = json_last_error();

    // Handle 204 No Content specifically
    if ($response === '' || ($decodedResponse === null && $jsonError === JSON_ERROR_NONE && $status_code === 204)) {
         return [
            'success' => true,
            'message' => 'Operation successful with no content.',
            'data' => null,
            'http_status_code' => $status_code
        ];
    }
    
    if ($decodedResponse === null && $jsonError !== JSON_ERROR_NONE) {
        // JSON decoding failed
        return [
            'success' => false,
            'message' => 'Invalid API response format (not JSON). Error: ' . json_last_error_msg(),
            'data' => null,
            'raw_response' => $response, // Include raw response for debugging
            'http_status_code' => $status_code
        ];
    }
    
    // Ensure the response is an array, even if the top-level JSON is not an object
    if (!is_array($decodedResponse)) {
        $decodedResponse = ['data' => $decodedResponse]; // Wrap scalar or other non-array responses
    }

    // Ensure standard fields are present
    if (!isset($decodedResponse['http_status_code'])) {
        $decodedResponse['http_status_code'] = $status_code;
    }
    if (!isset($decodedResponse['success'])) {
        // Assume success if HTTP status is 2xx, can be overridden by API's own 'success' field
        $decodedResponse['success'] = ($status_code >= 200 && $status_code < 300);
    }
    return $decodedResponse;
}

// Fetch initial data
$courseResp = callApi('course_api.php', 'GET');
$courses    = $courseResp['success'] ? ($courseResp['data'] ?? []) : [];

$catResp   = callApi('category_api.php', 'GET');
$categories = $catResp['success'] ? ($catResp['data'] ?? []) : [];

$instructorResp = callApi('instructor_api.php', 'GET');
$instructors = $instructorResp['success'] ? ($instructorResp['data'] ?? []) : [];

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
            max-height: calc(100vh - 260px); /* Adjust based on your modal header/footer height */
            overflow-y: auto;
        }
        .list-group-item span .btn { /* Style for buttons inside list items */
            padding: 0.1rem 0.3rem;
            font-size: 0.8rem;
        }
        #courseObjectivesList .list-group-item,
        #courseRequirementsList .list-group-item {
            padding: 0.5rem 0.75rem; /* Adjust padding for sub-items */
        }
        .sub-item-input-group .form-control {
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
        }
        .sub-item-input-group .btn {
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
        }
    </style>
</head>

<body>
    <?php include __DIR__ . '/template/dashboard.php'; // Assuming this file exists and is correctly pathed ?>
    <div class="main-content">
        <div class="container-fluid py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="mb-0">Quản lý Khóa học</h3>
                <button class="btn btn-primary add-new-course-btn">
                    <i class="bi bi-plus-lg me-1"></i> Thêm Khóa học
                </button>
            </div>

            <div id="alertPlaceholder"></div> <?php if (isset($_SESSION['success'])): ?>
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
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>STT</th>
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
                                    <td><?= htmlspecialchars($c['title'] ?? 'N/A') ?></td>
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
                                        $fullInstructorString = !empty($instructorNames) ? implode(', ', $instructorNames) : 'N/A';
                                        echo htmlspecialchars(truncate_text_with_ellipsis_raw($fullInstructorString, 30)); // Truncate if needed
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        $categoryNamesArray = [];
                                        if (!empty($c['categories']) && is_array($c['categories'])) {
                                            foreach ($c['categories'] as $category) {
                                                if (isset($category['categoryName'])) {
                                                    $categoryNamesArray[] = $category['categoryName']; // Raw name
                                                } elseif (isset($category['name'])) {
                                                    $categoryNamesArray[] = $category['name']; // Raw name
                                                }
                                            }
                                        }
                                        $fullCategoryString = !empty($categoryNamesArray) ? implode(', ', $categoryNamesArray) : 'N/A';
                                        echo htmlspecialchars(truncate_text_with_ellipsis_raw($fullCategoryString, 25, '......'));
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        $creator_raw = $c['createdByFullName'] ?? ($c['createdBy'] ?? 'N/A');
                                        echo htmlspecialchars(format_display_creator_raw($creator_raw));
                                        ?>
                                    </td>
                                    <td class="text-end action-buttons">
                                        <button class="btn btn-sm btn-outline-primary edit-course"
                                                data-course='<?= htmlspecialchars(json_encode($c, JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_HEX_QUOT), ENT_QUOTES, 'UTF-8') ?>'
                                                title="Sửa">
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

    <div class="modal fade" id="courseModal" tabindex="-1" aria-labelledby="courseModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <form id="courseForm" method="POST" action="../controller/c_course_management.php" enctype="multipart/form-data">
                    <input type="hidden" name="act" id="formAct" value="create">
                    <input type="hidden" name="CourseID" id="modalCourseID">
                    <div class="modal-header">
                        <h5 class="modal-title" id="courseModalLabel">Thêm Khóa học</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Tiêu đề <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="Title" id="modalTitle" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Giá (₫) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" name="Price" id="modalPrice" step="1000" min="0" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Giảng viên <span class="text-danger">*</span></label>
                                    <select class="form-select" name="Instructors[]" id="modalInstructors" multiple required>
                                        <?php if (!empty($instructors)): foreach ($instructors as $instructor): ?>
                                            <option value="<?= htmlspecialchars($instructor['instructorID']) ?>"><?= htmlspecialchars($instructor['firstName'] . " " . $instructor['lastName']) ?></option>
                                        <?php endforeach; endif; ?>
                                    </select>
                                    <small class="form-text text-muted">Giữ Ctrl/Cmd để chọn nhiều.</small>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Danh mục <span class="text-danger">*</span></label>
                                    <select class="form-select" name="Categories[]" id="modalCategories" multiple required>
                                        <?php if (!empty($categories)): foreach ($categories as $cat): ?>
                                            <option value="<?= htmlspecialchars($cat['id'] ?? $cat['categoryID']) ?>"><?= htmlspecialchars($cat['name'] ?? $cat['categoryName']) ?></option>
                                        <?php endforeach; endif; ?>
                                    </select>
                                    <small class="form-text text-muted">Giữ Ctrl/Cmd để chọn nhiều.</small>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Mô tả chi tiết</label>
                                    <textarea class="form-control" name="Description" id="modalDescription" rows="3"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Ảnh đại diện</label>
                                    <input type="file" class="form-control" name="CourseImage" id="modalCourseImage" accept="image/jpeg,image/png,image/webp">
                                    <img id="modalImagePreview" src="#" alt="Xem trước ảnh" class="mt-2 img-fluid rounded" style="max-height:150px; display:none;">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Mục tiêu khóa học</label>
                                    <div id="courseObjectivesList" class="list-group mb-2" style="max-height: 200px; overflow-y: auto;">
                                        </div>
                                    <div class="input-group sub-item-input-group">
                                        <input type="text" class="form-control" id="newObjectiveText" placeholder="Nhập mục tiêu mới">
                                        <button class="btn btn-outline-success" type="button" id="addObjectiveBtn"><i class="bi bi-plus-circle-fill"></i> Thêm</button>
                                    </div>
                                    <input type="hidden" id="editingObjectiveID"> </div>

                                <div class="mb-3">
                                    <label class="form-label">Yêu cầu khóa học</label>
                                    <div id="courseRequirementsList" class="list-group mb-2" style="max-height: 200px; overflow-y: auto;">
                                        </div>
                                    <div class="input-group sub-item-input-group">
                                        <input type="text" class="form-control" id="newRequirementText" placeholder="Nhập yêu cầu mới">
                                        <button class="btn btn-outline-success" type="button" id="addRequirementBtn"><i class="bi bi-plus-circle-fill"></i> Thêm</button>
                                    </div>
                                     <input type="hidden" id="editingRequirementID"> </div>
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
        // Make constants available to JavaScript
        const API_BASE_URL = '<?= API_BASE ?>';
        const APP_IMG_BASE_PATH = '<?= APP_BASE_PATH_FOR_IMAGES ?>'; // Used for constructing image paths if needed
        const USER_TOKEN = '<?= $_SESSION['user']['token'] ?? '' ?>'; // For API authorization
        const IMAGE_SERVING_SCRIPT_NAME = 'c_file_loader.php'; // Name of your image serving script
        const PROJECT_BASE = '<?= APP_ROOT_URL_BASE ?>' // Base URL of the project

        // Function to show dynamic alerts
        function showAlert(message, type = 'success', duration = 3000) {
            const alertPlaceholder = document.getElementById('alertPlaceholder');
            const wrapper = document.createElement('div');
            wrapper.innerHTML = [
                `<div class="alert alert-${type} alert-dismissible fade show" role="alert">`,
                `   <div>${message}</div>`,
                '   <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>',
                '</div>'
            ].join('');
            alertPlaceholder.append(wrapper);
            setTimeout(() => {
                if (wrapper) wrapper.remove(); // Remove the alert after duration
            }, duration);
        }
        
        // Centralized API fetch function using JavaScript's Fetch API
        async function fetchApi(endpoint, method = 'GET', payload = null) {
            let url = `${API_BASE_URL}/${endpoint}`;

            // Append query parameters for GET requests
            if (method.toUpperCase() === 'GET' && payload && Object.keys(payload).length > 0) {
                const queryParams = new URLSearchParams(payload);
                url += `?${queryParams.toString()}`;
            }

            const options = {
                method: method.toUpperCase(),
                headers: {
                    'Accept': 'application/json' // Expect JSON response
                }
            };
            if (USER_TOKEN) {
                options.headers['Authorization'] = `Bearer ${USER_TOKEN}`; // Add auth token if available
            }

            if (method.toUpperCase() !== 'GET' && method.toUpperCase() !== 'HEAD') {
                options.headers['Content-Type'] = 'application/json; charset=utf-8'; // Set content type for POST/PUT etc.
                options.body = payload ? JSON.stringify(payload) : JSON.stringify({}); // Ensure body is valid JSON
            }

            try {
                const response = await fetch(url, options);
                let responseData = {};

                if (response.status === 204) { // Handle No Content response
                    responseData = { success: true, message: 'Thao tác thành công, không có nội dung trả về.' };
                } else if (response.headers.get("content-type")?.includes("application/json")) {
                    responseData = await response.json(); // Parse JSON response
                } else {
                    const textResponse = await response.text(); // Handle non-JSON response as text
                    responseData = { 
                        success: response.ok, 
                        message: textResponse || (response.ok ? "Thao tác thành công" : "Lỗi không xác định"),
                        data: textResponse // Store raw text data
                    };
                }
                
                responseData.http_status_code = response.status; // Store HTTP status code
                if (typeof responseData.success === 'undefined') { // Infer success from HTTP status if not provided by API
                    responseData.success = response.ok;
                }
                return responseData;
            } catch (error) {
                console.error(`Lỗi gọi API cho ${method} ${url}:`, error);
                return { success: false, message: `Lỗi phía client: ${error.message}`, http_status_code: 0, data: null };
            }
        }


        document.addEventListener('DOMContentLoaded', () => {
            const courseModal = new bootstrap.Modal(document.getElementById('courseModal'));
            const form = document.getElementById('courseForm');
            const actInput = document.getElementById('formAct');
            const idInput = document.getElementById('modalCourseID');
            const titleInput = document.getElementById('modalTitle');
            const priceInput = document.getElementById('modalPrice');
            const instructorsSelect = document.getElementById('modalInstructors');
            const categoriesSelect = document.getElementById('modalCategories');
            const descriptionInput = document.getElementById('modalDescription');
            const imageInput = document.getElementById('modalCourseImage');
            const imagePreview = document.getElementById('modalImagePreview');
            const courseModalLabel = document.getElementById('courseModalLabel');

            // Sub-items (Objectives & Requirements) elements
            const objectivesListDiv = document.getElementById('courseObjectivesList');
            const newObjectiveText = document.getElementById('newObjectiveText');
            const addObjectiveBtn = document.getElementById('addObjectiveBtn');
            const editingObjectiveIDInput = document.getElementById('editingObjectiveID');

            const requirementsListDiv = document.getElementById('courseRequirementsList');
            const newRequirementText = document.getElementById('newRequirementText');
            const addRequirementBtn = document.getElementById('addRequirementBtn');
            const editingRequirementIDInput = document.getElementById('editingRequirementID');
            
            let currentCourseID = null; // To store the ID of the course being edited

            // Image preview handler
            imageInput.addEventListener('change', e => {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = ev => {
                        imagePreview.src = ev.target.result;
                        imagePreview.style.display = 'block';
                    };
                    reader.readAsDataURL(file);
                } else { // If no file is selected, revert to existing image or hide
                    if (!imagePreview.dataset.existingUrl) { // No existing image
                        imagePreview.src = '#';
                        imagePreview.style.display = 'none';
                    } else { // Revert to existing image
                        imagePreview.src = imagePreview.dataset.existingUrl;
                        imagePreview.style.display = 'block';
                    }
                }
            });

            // Function to reset UI for objectives and requirements
            function resetSubItemsUI() {
                objectivesListDiv.innerHTML = '';
                newObjectiveText.value = '';
                addObjectiveBtn.innerHTML = '<i class="bi bi-plus-circle-fill"></i> Thêm';
                addObjectiveBtn.classList.remove('btn-warning');
                addObjectiveBtn.classList.add('btn-outline-success');
                editingObjectiveIDInput.value = '';

                requirementsListDiv.innerHTML = '';
                newRequirementText.value = '';
                addRequirementBtn.innerHTML = '<i class="bi bi-plus-circle-fill"></i> Thêm';
                addRequirementBtn.classList.remove('btn-warning');
                addRequirementBtn.classList.add('btn-outline-success');
                editingRequirementIDInput.value = '';
            }

            // --- Objectives Management ---
            async function loadObjectives(courseID) {
                objectivesListDiv.innerHTML = '<div class="text-center text-muted p-2">Đang tải...</div>';
                const response = await fetchApi(`course_objective_api.php?courseID=${courseID}`);
                objectivesListDiv.innerHTML = ''; // Clear loading message
                if (response.success && response.data && Array.isArray(response.data)) {
                    if (response.data.length === 0) {
                        objectivesListDiv.innerHTML = '<div class="text-center text-muted p-2">Chưa có mục tiêu nào.</div>';
                    } else {
                        response.data.forEach(obj => renderObjective(obj));
                    }
                } else if (!response.success || !response.data) { // Handle API error or no data
                     objectivesListDiv.innerHTML = `<div class="text-danger p-2">Lỗi tải mục tiêu: ${response.message || 'Không có dữ liệu hoặc lỗi không xác định.'}</div>`;
                }
            }

            function renderObjective(obj) {
                const item = document.createElement('div');
                item.className = 'list-group-item d-flex justify-content-between align-items-center';
                item.dataset.id = obj.objectiveID;
                item.innerHTML = `
                    <span class="objective-text">${htmlspecialchars(obj.objective)}</span>
                    <span>
                        <button class="btn btn-sm btn-outline-primary edit-objective me-1" title="Sửa"><i class="bi bi-pencil-fill"></i></button>
                        <button class="btn btn-sm btn-outline-danger delete-objective" title="Xóa"><i class="bi bi-trash3-fill"></i></button>
                    </span>`;
                objectivesListDiv.appendChild(item);
            }

            addObjectiveBtn.addEventListener('click', async () => {
                const objectiveText = newObjectiveText.value.trim();
                if (!objectiveText || !currentCourseID) return;

                const editingID = editingObjectiveIDInput.value;
                let response;
                if (editingID) { // Update existing
                    response = await fetchApi('course_objective_api.php', 'PUT', { objectiveID: editingID, courseID: currentCourseID, objective: objectiveText });
                } else { // Create new
                    response = await fetchApi('course_objective_api.php', 'POST', { courseID: currentCourseID, objective: objectiveText });
                }

                if (response.success) {
                    showAlert(editingID ? 'Cập nhật mục tiêu thành công!' : 'Thêm mục tiêu thành công!');
                    newObjectiveText.value = '';
                    editingObjectiveIDInput.value = ''; // Reset editing state
                    addObjectiveBtn.innerHTML = '<i class="bi bi-plus-circle-fill"></i> Thêm';
                    addObjectiveBtn.classList.remove('btn-warning');
                    addObjectiveBtn.classList.add('btn-outline-success');
                    loadObjectives(currentCourseID); // Refresh list
                } else {
                    showAlert(`Lỗi: ${response.message || (editingID ? 'Không thể cập nhật mục tiêu.' : 'Không thể thêm mục tiêu.')}`, 'danger');
                }
            });

            objectivesListDiv.addEventListener('click', async (e) => {
                const target = e.target.closest('button');
                if (!target) return;

                const itemDiv = target.closest('.list-group-item');
                const objectiveID = itemDiv.dataset.id;

                if (target.classList.contains('edit-objective')) {
                    const objectiveText = itemDiv.querySelector('.objective-text').textContent;
                    newObjectiveText.value = objectiveText;
                    editingObjectiveIDInput.value = objectiveID;
                    addObjectiveBtn.innerHTML = '<i class="bi bi-save-fill"></i> Lưu sửa';
                    addObjectiveBtn.classList.remove('btn-outline-success');
                    addObjectiveBtn.classList.add('btn-warning');
                    newObjectiveText.focus();
                } else if (target.classList.contains('delete-objective')) {
                    if (confirm('Bạn có chắc muốn xóa mục tiêu này?')) {
                        const response = await fetchApi('course_objective_api.php', 'DELETE', { objectiveID });
                        if (response.success) {
                            showAlert('Xóa mục tiêu thành công!');
                            loadObjectives(currentCourseID); // Refresh list
                        } else {
                            showAlert(`Lỗi: ${response.message || 'Không thể xóa mục tiêu.'}`, 'danger');
                        }
                    }
                }
            });

            // --- Requirements Management (similar to Objectives) ---
            async function loadRequirements(courseID) {
                requirementsListDiv.innerHTML = '<div class="text-center text-muted p-2">Đang tải...</div>';
                const response = await fetchApi(`course_requirement_api.php?courseID=${courseID}`);
                requirementsListDiv.innerHTML = ''; // Clear loading message
                if (response.success && response.data && Array.isArray(response.data)) {
                     if (response.data.length === 0) {
                        requirementsListDiv.innerHTML = '<div class="text-center text-muted p-2">Chưa có yêu cầu nào.</div>';
                    } else {
                        response.data.forEach(req => renderRequirement(req));
                    }
                } else if (!response.success || !response.data) {
                    requirementsListDiv.innerHTML = `<div class="text-danger p-2">Lỗi tải yêu cầu: ${response.message || 'Không có dữ liệu hoặc lỗi không xác định.'}</div>`;
                }
            }

            function renderRequirement(req) {
                const item = document.createElement('div');
                item.className = 'list-group-item d-flex justify-content-between align-items-center';
                item.dataset.id = req.requirementID;
                item.innerHTML = `
                    <span class="requirement-text">${htmlspecialchars(req.requirement)}</span>
                    <span>
                        <button class="btn btn-sm btn-outline-primary edit-requirement me-1" title="Sửa"><i class="bi bi-pencil-fill"></i></button>
                        <button class="btn btn-sm btn-outline-danger delete-requirement" title="Xóa"><i class="bi bi-trash3-fill"></i></button>
                    </span>`;
                requirementsListDiv.appendChild(item);
            }
            
            addRequirementBtn.addEventListener('click', async () => {
                const requirementText = newRequirementText.value.trim();
                if (!requirementText || !currentCourseID) return;

                const editingID = editingRequirementIDInput.value;
                let response;
                if (editingID) { // Update existing
                    response = await fetchApi('course_requirement_api.php', 'PUT', { requirementID: editingID, courseID: currentCourseID, requirement: requirementText });
                } else { // Create new
                    response = await fetchApi('course_requirement_api.php', 'POST', { courseID: currentCourseID, requirement: requirementText });
                }

                if (response.success) {
                    showAlert(editingID ? 'Cập nhật yêu cầu thành công!' : 'Thêm yêu cầu thành công!');
                    newRequirementText.value = '';
                    editingRequirementIDInput.value = ''; // Reset editing state
                    addRequirementBtn.innerHTML = '<i class="bi bi-plus-circle-fill"></i> Thêm';
                    addRequirementBtn.classList.remove('btn-warning');
                    addRequirementBtn.classList.add('btn-outline-success');
                    loadRequirements(currentCourseID); // Refresh list
                } else {
                    showAlert(`Lỗi: ${response.message || (editingID ? 'Không thể cập nhật yêu cầu.' : 'Không thể thêm yêu cầu.')}`, 'danger');
                }
            });

            requirementsListDiv.addEventListener('click', async (e) => {
                const target = e.target.closest('button');
                if (!target) return;

                const itemDiv = target.closest('.list-group-item');
                const requirementID = itemDiv.dataset.id;

                if (target.classList.contains('edit-requirement')) {
                    const requirementText = itemDiv.querySelector('.requirement-text').textContent;
                    newRequirementText.value = requirementText;
                    editingRequirementIDInput.value = requirementID;
                    addRequirementBtn.innerHTML = '<i class="bi bi-save-fill"></i> Lưu sửa';
                    addRequirementBtn.classList.remove('btn-outline-success');
                    addRequirementBtn.classList.add('btn-warning');
                    newRequirementText.focus();
                } else if (target.classList.contains('delete-requirement')) {
                     if (confirm('Bạn có chắc muốn xóa yêu cầu này?')) {
                        const response = await fetchApi('course_requirement_api.php', 'DELETE', { requirementID });
                        if (response.success) {
                            showAlert('Xóa yêu cầu thành công!');
                            loadRequirements(currentCourseID); // Refresh list
                        } else {
                            showAlert(`Lỗi: ${response.message || 'Không thể xóa yêu cầu.'}`, 'danger');
                        }
                    }
                }
            });


            // JS equivalent of PHP's htmlspecialchars
            function htmlspecialchars(str) {
                if (typeof str !== 'string') return '';
                const map = {
                    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
                };
                return str.replace(/[&<>"']/g, function(m) { return map[m]; });
            }

            // Handle "Add New Course" button
            document.querySelectorAll('.add-new-course-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    form.reset();
                    actInput.value = 'create';
                    idInput.value = '';
                    currentCourseID = null; // Ensure no course ID is set for new course
                    courseModalLabel.textContent = 'Thêm Khóa học';

                    // Clear multi-selects
                    Array.from(instructorsSelect.options).forEach(option => option.selected = false);
                    Array.from(categoriesSelect.options).forEach(option => option.selected = false);
                    
                    // Reset image preview
                    imagePreview.src = '#';
                    imagePreview.style.display = 'none';
                    imagePreview.removeAttribute('data-existing-url'); // Clear existing image URL
                    imageInput.value = ''; // Clear file input

                    resetSubItemsUI(); // Reset objectives and requirements
                    courseModal.show();
                });
            });

            // Handle "Edit Course" buttons
            document.querySelectorAll('.edit-course').forEach(btn => {
                btn.addEventListener('click', async () => { // Make async for await fetchApi
                    const dataString = btn.getAttribute('data-course');
                    if (!dataString) {
                        console.error('data-course attribute is missing or empty');
                        showAlert('Lỗi: Không thể tải dữ liệu khóa học.', 'danger');
                        return;
                    }
                    try {
                        const data = JSON.parse(dataString);
                        form.reset(); // Reset form first
                        actInput.value = 'update';
                        idInput.value = data.courseID || '';
                        currentCourseID = data.courseID; // Set current course ID for sub-items
                        titleInput.value = data.title || '';
                        priceInput.value = data.price || '0';
                        descriptionInput.value = data.description || '';
                        courseModalLabel.textContent = 'Sửa Khóa học';

                        // Pre-select instructors
                        Array.from(instructorsSelect.options).forEach(opt => opt.selected = false); // Deselect all first
                        if (data.instructors && Array.isArray(data.instructors)) {
                            const selectedInstructorIDs = data.instructors.map(instr => instr.instructorID.toString());
                            Array.from(instructorsSelect.options).forEach(option => {
                                if (selectedInstructorIDs.includes(option.value)) {
                                    option.selected = true;
                                }
                            });
                        }

                        // Pre-select categories
                        Array.from(categoriesSelect.options).forEach(opt => opt.selected = false); // Deselect all first
                        if (data.categories && Array.isArray(data.categories)) {
                            const selectedCategoryIDs = data.categories.map(cat => (cat.id || cat.categoryID).toString()); // Handle both 'id' and 'categoryID'
                            Array.from(categoriesSelect.options).forEach(option => {
                                if (selectedCategoryIDs.includes(option.value)) {
                                    option.selected = true;
                                }
                            });
                        }
                        
                        // Reset and then load existing image if available
                        imageInput.value = ''; // Clear file input
                        imagePreview.src = '#';
                        imagePreview.style.display = 'none';
                        imagePreview.removeAttribute('data-existing-url');

                        if (currentCourseID) { // Fetch and display current course image
                            // Assuming you have an API endpoint to get the image path or a direct URL structure
                            // This is a placeholder for how you might fetch the image URL
                            // You might need to adjust this based on your actual image serving mechanism
                            const imgResponse = await fetchApi(`course_image_api.php?courseID=${currentCourseID}`);
                            if (imgResponse.success && imgResponse.data && imgResponse.data.length > 0) {
                                const imagePathFromServer = imgResponse.data[0].imagePath; // Assuming API returns { imagePath: 'path/to/image.jpg' }
                                if (imagePathFromServer) {
                                    // Construct the full URL to the image.
                                    // This might involve PROJECT_BASE or a specific image serving script.
                                    const imageName = imagePathFromServer.split('/').pop(); // Get filename
                                    const imageUrl = `${PROJECT_BASE}/controller/${IMAGE_SERVING_SCRIPT_NAME}?act=serve_image&course_id=${currentCourseID}&image=${encodeURIComponent(imageName)}`;
                                    
                                    imagePreview.src = imageUrl;
                                    imagePreview.style.display = 'block';
                                    imagePreview.dataset.existingUrl = imageUrl; // Store for later if user deselects new image
                                }
                            }
                        }


                        resetSubItemsUI(); // Clear previous sub-items
                        if (currentCourseID) { // Load objectives and requirements for the current course
                            loadObjectives(currentCourseID);
                            loadRequirements(currentCourseID);
                        }

                        courseModal.show();

                    } catch (e) {
                        console.error('Error parsing data-course JSON or setting up modal:', e);
                        showAlert('Lỗi: Không thể xử lý dữ liệu khóa học để sửa. ' + e.message, 'danger');
                        console.error('Problematic JSON string:', dataString); // Log the problematic string
                    }
                });
            });

            // Handle "Delete Course" buttons
            document.querySelectorAll('.delete-course').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.preventDefault(); // Prevent default if it's a link/submit
                    if (!confirm('Bạn có chắc muốn xóa khóa học này? Hành động này không thể hoàn tác.')) return;
                    
                    const courseIdToDelete = btn.getAttribute('data-id');
                    if (!courseIdToDelete) {
                        showAlert('Lỗi: Không tìm thấy ID khóa học để xóa.', 'danger');
                        return;
                    }
                    // Redirect to the controller for deletion
                    window.location.href = `../controller/c_course_management.php?act=delete&courseID=${encodeURIComponent(courseIdToDelete)}`;
                });
            });

            // Reset modal on hide
            document.getElementById('courseModal').addEventListener('hidden.bs.modal', function () {
                form.reset();
                actInput.value = 'create';
                idInput.value = '';
                currentCourseID = null;

                Array.from(instructorsSelect.options).forEach(option => option.selected = false);
                Array.from(categoriesSelect.options).forEach(option => option.selected = false);

                imagePreview.src = '#';
                imagePreview.style.display = 'none';
                imagePreview.removeAttribute('data-existing-url');
                imageInput.value = '';

                resetSubItemsUI();
                courseModalLabel.textContent = 'Thêm Khóa học'; // Reset title
            });

        });
    </script>
</body>
</html>
