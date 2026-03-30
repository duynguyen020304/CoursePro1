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
define('DEFAULT_PAGE_SIZE', 12);

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
            'method' => $methodUpper,
            'header' => $headers,
            'ignore_errors' => true,
            'timeout' => 15,
        ]
    ];

    if ($methodUpper !== 'GET' && $methodUpper !== 'HEAD') {
        if (!empty($payload)) {
            $options['http']['content'] = json_encode($payload);
        } else if (in_array($methodUpper, ['POST', 'PUT', 'PATCH'])) {
            $options['http']['content'] = '{}';
        }
    }

    $context = stream_context_create($options);
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

$catResp = callApi('category_api.php', 'GET');
$categories = $catResp['success'] ? ($catResp['data'] ?? []) : [];

$instructorResp = callApi('instructor_api.php', 'GET');
$instructors = $instructorResp['success'] ? ($instructorResp['data'] ?? []) : [];

$languages = [
    ["language" => "Arabic", "locale" => "ar"],
    ["language" => "French", "locale" => "fr"],
    ["language" => "German", "locale" => "de"],
    ["language" => "Hindi", "locale" => "hi"],
    ["language" => "Indonesian", "locale" => "id"],
    ["language" => "Italian", "locale" => "it"],
    ["language" => "Japanese", "locale" => "ja"],
    ["language" => "Korean", "locale" => "ko"],
    ["language" => "Mandarin", "locale" => "zh"],
    ["language" => "Polish", "locale" => "pl"],
    ["language" => "Portuguese", "locale" => "pt"],
    ["language" => "Russian", "locale" => "ru"],
    ["language" => "Spanish", "locale" => "es"],
    ["language" => "Turkish", "locale" => "tr"],
    ["language" => "Vietnamese", "locale" => "vi"]
];

$language_locale_map = [];
foreach ($languages as $lang) {
    $language_locale_map[$lang['language']] = $lang['locale'];
}

$locale_language_map = [];
foreach ($languages as $lang) {
    $locale_language_map[$lang['locale']] = $lang['language'];
}

$difficulties = ["Beginner", "Intermediate", "Expert"];

?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8"/>
    <title>Quản lý Khóa học</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.css" rel="stylesheet" />
    <link href="../assets/css/base_dashboard.css" rel="stylesheet" />
    <link href="../assets/css/admin_style.css" rel="stylesheet" />
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

        .list-group-item span .btn {
            padding: 0.1rem 0.3rem;
            font-size: 0.8rem;
        }

        #courseObjectivesList .list-group-item,
        #courseRequirementsList .list-group-item {
            padding: 0.5rem 0.75rem;
        }

        .sub-item-input-group .form-control {
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
        }

        .sub-item-input-group .btn {
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
        }

        .filter-controls .form-select,
        .filter-controls .form-control {
            font-size: 0.9rem;
        }

        .table th,
        .table td {
            vertical-align: middle;
        }
    </style>
</head>

<body>
<?php include __DIR__ . '/../templates/dashboard.php'; ?>
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
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="card shadow-sm mb-4">
            <div class="card-body filter-controls">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label for="searchInput" class="form-label">Tìm kiếm</label>
                        <input type="text" id="searchInput" class="form-control"
                               placeholder="ID hoặc Tiêu đề khóa học...">
                    </div>
                    <div class="col-md-3">
                        <label for="difficultyFilter" class="form-label">Độ khó</label>
                        <select id="difficultyFilter" class="form-select">
                            <option value="">Tất cả Độ khó</option>
                            <?php foreach ($difficulties as $diff): ?>
                                <option value="<?= htmlspecialchars($diff) ?>"><?= htmlspecialchars($diff) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="languageFilter" class="form-label">Ngôn ngữ</label>
                        <select id="languageFilter" class="form-select">
                            <option value="">Tất cả Ngôn ngữ</option>
                            <?php foreach ($languages as $lang): ?>
                                <option value="<?= htmlspecialchars($lang['locale']) ?>"><?= htmlspecialchars($lang['language']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button id="applyFiltersBtn" class="btn btn-info w-100"><i class="bi bi-funnel-fill"></i> Lọc
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                <tr>
                    <th>STT</th>
                    <th>Tiêu đề</th>
                    <th>Giá (₫)</th>
                    <th>Giảng viên</th>
                    <th>Danh mục</th>
                    <th>Độ khó</th>
                    <th>Ngôn ngữ</th>
                    <th class="text-end">Hành động</th>
                </tr>
                </thead>
                <tbody id="coursesTableBody">
                <tr>
                    <td colspan="8" class="text-center p-5">
                        <div class="spinner-border text-primary" role="status"><span
                                    class="visually-hidden">Đang tải...</span></div>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <nav aria-label="Course navigation">
            <ul class="pagination justify-content-center" id="paginationControls">
                <li class="page-item" id="prevPageItem">
                    <button class="page-link" id="prevPageLink">Trước</button>
                </li>
                <li class="page-item disabled"><span class="page-link" id="currentPageNumber">1</span></li>
                <li class="page-item" id="nextPageItem">
                    <button class="page-link" id="nextPageLink">Sau</button>
                </li>
            </ul>
        </nav>
    </div>
</div>

<div class="modal fade" id="courseModal" tabindex="-1" aria-labelledby="courseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <form id="courseForm" method="POST" action="../../backend/Controller/Form/c_course_management.php"
                  enctype="multipart/form-data">
                <input type="hidden" name="act" id="formAct" value="create"/>
                <input type="hidden" name="CourseID" id="modalCourseID"/>
                <div class="modal-header">
                    <h5 class="modal-title" id="courseModalLabel">Thêm Khóa học</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Tiêu đề <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="Title" id="modalTitle" required/>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Giá (₫) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" name="Price" id="modalPrice" step="1000"
                                           min="0" required/>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Độ khó <span class="text-danger">*</span></label>
                                    <select class="form-select" name="Difficulty" id="modalDifficulty" required>
                                        <option value="" disabled selected>Chọn độ khó</option>
                                        <?php foreach ($difficulties as $diff): ?>
                                            <option value="<?= htmlspecialchars($diff) ?>"><?= htmlspecialchars($diff) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Ngôn ngữ <span class="text-danger">*</span></label>
                                <select class="form-select" name="Language" id="modalLanguage" required>
                                    <option value="" disabled selected>Chọn ngôn ngữ</option>
                                    <?php foreach ($languages as $lang): ?>
                                        <option value="<?= htmlspecialchars($lang['locale']) ?>"><?= htmlspecialchars($lang['language']) ?></option>

                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Giảng viên <span class="text-danger">*</span></label>
                                <select class="form-select" name="Instructors[]" id="modalInstructors" multiple
                                        required>
                                    <?php if (!empty($instructors)): foreach ($instructors as $instructor): ?>
                                        <option value="<?= htmlspecialchars($instructor['instructorID']) ?>"><?= htmlspecialchars($instructor['firstName'] . " " . $instructor['lastName']) ?></option>
                                    <?php endforeach;
                                    endif; ?>
                                </select>
                                <small class="form-text text-muted">Giữ Ctrl/Cmd để chọn nhiều.</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Danh mục <span class="text-danger">*</span></label>
                                <select class="form-select" name="Categories[]" id="modalCategories" multiple required>
                                    <?php if (!empty($categories)): foreach ($categories as $cat): ?>
                                        <option value="<?= htmlspecialchars($cat['id'] ?? $cat['categoryID']) ?>"><?= htmlspecialchars($cat['name'] ?? $cat['categoryName']) ?></option>
                                    <?php endforeach;
                                    endif; ?>
                                </select>
                                <small class="form-text text-muted">Giữ Ctrl/Cmd để chọn nhiều.</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Mô tả chi tiết</label>
                                <textarea class="form-control" name="Description" id="modalDescription"
                                          rows="3"></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Ảnh đại diện</label>
                                <input type="file" class="form-control" name="CourseImage" id="modalCourseImage"
                                       accept="image/jpeg,image/png,image/webp"/>
                                <img id="modalImagePreview" src="#" alt="Xem trước ảnh" class="mt-2 img-fluid rounded"
                                     style="max-height:150px; display:none;"/>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Mục tiêu khóa học</label>
                                <div id="courseObjectivesList" class="list-group mb-2"
                                     style="max-height: 200px; overflow-y: auto;"></div>
                                <div class="input-group sub-item-input-group">
                                    <input type="text" class="form-control" id="newObjectiveText"
                                           placeholder="Nhập mục tiêu mới"/>
                                    <button class="btn btn-outline-success" type="button" id="addObjectiveBtn"><i
                                                class="bi bi-plus-circle-fill"></i> Thêm
                                    </button>
                                </div>
                                <input type="hidden" id="editingObjectiveID"/>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Yêu cầu khóa học</label>
                                <div id="courseRequirementsList" class="list-group mb-2"
                                     style="max-height: 200px; overflow-y: auto;"></div>
                                <div class="input-group sub-item-input-group">
                                    <input type="text" class="form-control" id="newRequirementText"
                                           placeholder="Nhập yêu cầu mới"/>
                                    <button class="btn btn-outline-success" type="button" id="addRequirementBtn"><i
                                                class="bi bi-plus-circle-fill"></i> Thêm
                                    </button>
                                </div>
                                <input type="hidden" id="editingRequirementID"/>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save-fill me-1"></i> Lưu thay đổi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="../assets/js/bootstrap.bundle.min.js"></script>
<script>
    const API_BASE_URL = '<?= API_BASE ?>';
    const APP_IMG_BASE_PATH = '<?= APP_BASE_PATH_FOR_IMAGES ?>';
    const USER_TOKEN = '<?= $_SESSION['user']['token'] ?? '' ?>';
    const IMAGE_SERVING_SCRIPT_NAME = 'c_file_loader.php';
    const PROJECT_BASE = '<?= APP_ROOT_URL_BASE ?>';
    const DEFAULT_PAGE_SIZE_JS = <?= DEFAULT_PAGE_SIZE ?>;

    const LANGUAGE_LOCALE_MAP = <?= json_encode($language_locale_map) ?>;
    const LOCALE_LANGUAGE_MAP = <?= json_encode($locale_language_map) ?>;
    const DIFFICULTIES_JS = <?= json_encode($difficulties) ?>;

    let currentPage = 1;
    let currentSearchTerm = '';
    let currentDifficultyFilter = '';
    let currentLanguageFilter = '';
    let allCoursesCurrentPage = [];

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

        const bsAlert = new bootstrap.Alert(wrapper.querySelector('.alert'));
        setTimeout(() => {
            bsAlert.close();
        }, duration);
    }

    async function fetchApi(endpoint, method = 'GET', payload = null) {
        let url = `${API_BASE_URL}/${endpoint}`;
        if (method.toUpperCase() === 'GET' && payload && Object.keys(payload).length > 0) {
            const queryParams = new URLSearchParams(payload);
            url += `?${queryParams.toString()}`;
        }
        const options = {
            method: method.toUpperCase(),
            headers: {
                'Accept': 'application/json'
            }
        };
        if (USER_TOKEN) {
            options.headers['Authorization'] = `Bearer ${USER_TOKEN}`;
        }
        if (method.toUpperCase() !== 'GET' && method.toUpperCase() !== 'HEAD') {
            options.headers['Content-Type'] = 'application/json; charset=utf-8';
            options.body = payload ? JSON.stringify(payload) : JSON.stringify({});
        }
        try {
            const response = await fetch(url, options);
            let responseData = {};
            if (response.status === 204) {
                responseData = {
                    success: true,
                    message: 'Thao tác thành công, không có nội dung trả về.'
                };
            } else if (response.headers.get("content-type")?.includes("application/json")) {
                responseData = await response.json();
            } else {
                const textResponse = await response.text();
                responseData = {
                    success: response.ok,
                    message: textResponse || (response.ok ? "Thao tác thành công" : "Lỗi không xác định từ máy chủ"),
                    data: textResponse
                };
            }
            responseData.http_status_code = response.status;
            if (typeof responseData.success === 'undefined') {
                responseData.success = response.ok;
            }
            return responseData;
        } catch (error) {
            console.error(`Lỗi gọi API cho ${method} ${url}:`, error);
            return {
                success: false,
                message: `Lỗi phía client: ${error.message}`,
                http_status_code: 0,
                data: null
            };
        }
    }

    function htmlspecialchars(str) {
        if (typeof str !== 'string' && typeof str !== 'number') return '';
        str = String(str);
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return str.replace(/[&<>"']/g, function (m) {
            return map[m];
        });
    }

    function truncateText(text, maxLength = 20, ellipsis = '...') {
        if (text === null || text === undefined || text === 'N/A') {
            return 'N/A';
        }
        const strText = String(text);
        if (strText.length > maxLength) {
            return strText.substring(0, maxLength) + ellipsis;
        }
        return strText;
    }

    function renderCoursesTable(courses) {
        const tableBody = document.getElementById('coursesTableBody');
        tableBody.innerHTML = '';

        if (!courses || courses.length === 0) {
            tableBody.innerHTML = `<tr><td colspan="8" class="text-center">Không tìm thấy khóa học nào.</td></tr>`;
            return;
        }

        courses.forEach((course, index) => {
            const languageLocale = course.language;
            const languageFullName = LOCALE_LANGUAGE_MAP[languageLocale] || languageLocale || 'N/A';
            const row = tableBody.insertRow();
            row.innerHTML = `
            <td>${(currentPage - 1) * DEFAULT_PAGE_SIZE_JS + index + 1}</td>
            <td>${htmlspecialchars(truncateText(course.title, 30))}</td>
            <td>${new Intl.NumberFormat('vi-VN').format(course.price || 0)} ₫</td>
            <td>
                ${truncateText(course.instructors && Array.isArray(course.instructors) ? course.instructors.map(i => `${i.firstName || ''} ${i.lastName || ''}`.trim()).filter(name => name).join(', ') : 'N/A', 25)}
            </td>
            <td>
                ${truncateText(course.categories && Array.isArray(course.categories) ? course.categories.map(c => c.categoryName || c.name).join(', ') : 'N/A', 25)}
            </td>
            <td>${htmlspecialchars(course.difficulty || 'N/A')}</td>
            <td>${htmlspecialchars(languageFullName)}</td>  <td class="text-end action-buttons">
                <button class="btn btn-sm btn-outline-primary edit-course"
                        data-course='${htmlspecialchars(JSON.stringify(course))}'
                        title="Sửa" type="button">
                    <i class="bi bi-pencil-square"></i>
                </button>
                <button class="btn btn-sm btn-outline-danger delete-course" data-id="${htmlspecialchars(course.courseID || '')}" title="Xóa">
                    <i class="bi bi-trash3-fill"></i>
                </button>
            </td>
        `;
        });
        addEventListenersToTableButtons();
    }

    function addEventListenersToTableButtons() {
        document.querySelectorAll('.edit-course').forEach(btn => {
            const newBtn = btn.cloneNode(true);
            btn.parentNode.replaceChild(newBtn, btn);
            newBtn.addEventListener('click', handleEditCourse);
        });
        document.querySelectorAll('.delete-course').forEach(btn => {
            const newBtn = btn.cloneNode(true);
            btn.parentNode.replaceChild(newBtn, btn);
            newBtn.addEventListener('click', handleDeleteCourse);
        });
    }

    function updatePaginationControls(numItemsOnPage) {
        document.getElementById('currentPageNumber').textContent = currentPage;
        const prevButton = document.getElementById('prevPageLink');
        const nextButton = document.getElementById('nextPageLink');

        prevButton.disabled = currentPage === 1;
        document.getElementById('prevPageItem').classList.toggle('disabled', currentPage === 1);

        nextButton.disabled = numItemsOnPage < DEFAULT_PAGE_SIZE_JS;
        document.getElementById('nextPageItem').classList.toggle('disabled', numItemsOnPage < DEFAULT_PAGE_SIZE_JS);
    }

    async function loadCourses(page = 1) {
        currentPage = page;
        const tableBody = document.getElementById('coursesTableBody');
        const paginationControls = document.getElementById('paginationControls');
        tableBody.innerHTML = `<tr><td colspan="8" class="text-center p-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Đang tải...</span></div></td></tr>`;

        let response;
        if (currentSearchTerm) {
            paginationControls.style.display = 'none';

            const params = {
                title: currentSearchTerm,
                isGetForCourseManagement: true,
            };
            if (currentDifficultyFilter) params.difficulty = currentDifficultyFilter;
            if (currentLanguageFilter) params.language = currentLanguageFilter;

            response = await fetchApi('search_course_api.php', 'GET', params);
            console.log(response)

        } else {
            paginationControls.style.display = 'flex';

            const params = {
                page: currentPage,
                pageSize: DEFAULT_PAGE_SIZE_JS
            };
            if (currentDifficultyFilter) params.difficulty = currentDifficultyFilter;
            if (currentLanguageFilter) params.language = currentLanguageFilter;

            response = await fetchApi('course_api.php', 'GET', params);
        }

        if (response.success && response.data) {
            renderCoursesTable(response.data);
            if (!currentSearchTerm) {
                updatePaginationControls(response.data.length);
            }
        } else {
            const message = response.message || 'Lỗi không xác định';
            tableBody.innerHTML = `<tr><td colspan="8" class="text-center text-danger">Lỗi: ${message}</td></tr>`;
            if (!currentSearchTerm) {
                updatePaginationControls(0);
            }
        }
    }

    const courseModalElement = document.getElementById('courseModal');
    const courseModal = new bootstrap.Modal(courseModalElement);
    const form = document.getElementById('courseForm');
    const actInput = document.getElementById('formAct');
    const idInput = document.getElementById('modalCourseID');
    const titleInput = document.getElementById('modalTitle');
    const priceInput = document.getElementById('modalPrice');
    const difficultySelect = document.getElementById('modalDifficulty');
    const languageSelect = document.getElementById('modalLanguage');
    const instructorsSelect = document.getElementById('modalInstructors');
    const categoriesSelect = document.getElementById('modalCategories');
    const descriptionInput = document.getElementById('modalDescription');
    const imageInput = document.getElementById('modalCourseImage');
    const imagePreview = document.getElementById('modalImagePreview');
    const courseModalLabel = document.getElementById('courseModalLabel');

    const objectivesListDiv = document.getElementById('courseObjectivesList');
    const newObjectiveText = document.getElementById('newObjectiveText');
    const addObjectiveBtn = document.getElementById('addObjectiveBtn');
    const editingObjectiveIDInput = document.getElementById('editingObjectiveID');

    const requirementsListDiv = document.getElementById('courseRequirementsList');
    const newRequirementText = document.getElementById('newRequirementText');
    const addRequirementBtn = document.getElementById('addRequirementBtn');
    const editingRequirementIDInput = document.getElementById('editingRequirementID');
    let currentCourseIDForSubItems = null;


    imageInput.addEventListener('change', e => {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = ev => {
                imagePreview.src = ev.target.result;
                imagePreview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else {
            if (!imagePreview.dataset.existingUrl) {
                imagePreview.src = '#';
                imagePreview.style.display = 'none';
            } else {
                imagePreview.src = imagePreview.dataset.existingUrl;
                imagePreview.style.display = 'block';
            }
        }
    });

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

    async function loadObjectives(courseID) {
        if (!courseID) {
            objectivesListDiv.innerHTML = '<div class="text-center text-muted p-2">Vui lòng lưu khóa học trước khi thêm mục tiêu.</div>';
            return;
        }
        objectivesListDiv.innerHTML = '<div class="text-center text-muted p-2">Đang tải...</div>';
        const response = await fetchApi(`course_objective_api.php?courseID=${courseID}`);
        objectivesListDiv.innerHTML = '';
        if (response.success && response.data && Array.isArray(response.data)) {
            if (response.data.length === 0) {
                objectivesListDiv.innerHTML = '<div class="text-center text-muted p-2">Chưa có mục tiêu nào.</div>';
            } else {
                response.data.forEach(obj => renderObjective(obj));
            }
        } else if (!response.success || !response.data) {
            objectivesListDiv.innerHTML = `<div class="text-danger p-2">Lỗi tải mục tiêu: ${response.message || 'Không có dữ liệu hoặc lỗi không xác định.'}</div>`;
        }
    }

    function renderObjective(obj) {
        const item = document.createElement('div');
        item.className = 'list-group-item d-flex justify-content-between align-items-center';
        item.dataset.id = obj.objectiveID;
        item.innerHTML = `
                <span class="objective-text">${htmlspecialchars(obj.objective)}</span>
                <span class="d-inline-flex"> <button class="btn btn-sm btn-outline-primary edit-objective me-1" type="button" title="Sửa"><i class="bi bi-pencil-fill"></i></button>
                    <button class="btn btn-sm btn-outline-danger delete-objective" type="button" title="Xóa"><i class="bi bi-trash3-fill"></i></button>
                </span>`;
        objectivesListDiv.appendChild(item);
    }

    addObjectiveBtn.addEventListener('click', async () => {
        const objectiveText = newObjectiveText.value.trim();
        if (!objectiveText || !currentCourseIDForSubItems) {
            showAlert('Vui lòng nhập nội dung mục tiêu. Nếu là khóa học mới, hãy lưu khóa học trước.', 'warning');
            return;
        }

        const editingID = editingObjectiveIDInput.value;
        let response;
        const payload = {
            courseID: currentCourseIDForSubItems,
            objective: objectiveText
        };
        if (editingID) {
            payload.objectiveID = editingID;
            response = await fetchApi('course_objective_api.php', 'PUT', payload);
        } else {
            response = await fetchApi('course_objective_api.php', 'POST', payload);
        }

        if (response.success) {
            showAlert(editingID ? 'Cập nhật mục tiêu thành công!' : 'Thêm mục tiêu thành công!');
            newObjectiveText.value = '';
            editingObjectiveIDInput.value = '';
            addObjectiveBtn.innerHTML = '<i class="bi bi-plus-circle-fill"></i> Thêm';
            addObjectiveBtn.classList.remove('btn-warning');
            addObjectiveBtn.classList.add('btn-outline-success');
            loadObjectives(currentCourseIDForSubItems);
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
            if (window.confirm('Bạn có chắc muốn xóa mục tiêu này?')) { // Consider custom modal for confirm
                const response = await fetchApi('course_objective_api.php', 'DELETE', {
                    objectiveID
                });
                if (response.success) {
                    showAlert('Xóa mục tiêu thành công!');
                    loadObjectives(currentCourseIDForSubItems);
                } else {
                    showAlert(`Lỗi: ${response.message || 'Không thể xóa mục tiêu.'}`, 'danger');
                }
            }
        }
    });

    async function loadRequirements(courseID) {
        if (!courseID) {
            requirementsListDiv.innerHTML = '<div class="text-center text-muted p-2">Vui lòng lưu khóa học trước khi thêm yêu cầu.</div>';
            return;
        }
        requirementsListDiv.innerHTML = '<div class="text-center text-muted p-2">Đang tải...</div>';
        const response = await fetchApi(`course_requirement_api.php?courseID=${courseID}`);
        requirementsListDiv.innerHTML = '';
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
                <span class="d-inline-flex"> <button class="btn btn-sm btn-outline-primary edit-requirement me-1" type="button" title="Sửa"><i class="bi bi-pencil-fill"></i></button> <button class="btn btn-sm btn-outline-danger delete-requirement" type="button" title="Xóa"><i class="bi bi-trash3-fill"></i></button>
                </span>`;
        requirementsListDiv.appendChild(item);
    }

    addRequirementBtn.addEventListener('click', async () => {
        const requirementText = newRequirementText.value.trim();
        if (!requirementText || !currentCourseIDForSubItems) {
            showAlert('Vui lòng nhập nội dung yêu cầu. Nếu là khóa học mới, hãy lưu khóa học trước.', 'warning');
            return;
        }

        const editingID = editingRequirementIDInput.value;
        let response;
        const payload = {
            courseID: currentCourseIDForSubItems,
            requirement: requirementText
        };

        if (editingID) {
            payload.requirementID = editingID;
            response = await fetchApi('course_requirement_api.php', 'PUT', payload);
        } else {
            response = await fetchApi('course_requirement_api.php', 'POST', payload);
        }

        if (response.success) {
            showAlert(editingID ? 'Cập nhật yêu cầu thành công!' : 'Thêm yêu cầu thành công!');
            newRequirementText.value = '';
            editingRequirementIDInput.value = '';
            addRequirementBtn.innerHTML = '<i class="bi bi-plus-circle-fill"></i> Thêm';
            addRequirementBtn.classList.remove('btn-warning');
            addRequirementBtn.classList.add('btn-outline-success');
            loadRequirements(currentCourseIDForSubItems);
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
            if (window.confirm('Bạn có chắc muốn xóa yêu cầu này?')) { // Consider custom modal
                const response = await fetchApi('course_requirement_api.php', 'DELETE', {
                    requirementID
                });
                if (response.success) {
                    showAlert('Xóa yêu cầu thành công!');
                    loadRequirements(currentCourseIDForSubItems);
                } else {
                    showAlert(`Lỗi: ${response.message || 'Không thể xóa yêu cầu.'}`, 'danger');
                }
            }
        }
    });

    document.querySelectorAll('.add-new-course-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            form.reset();
            actInput.value = 'create';
            idInput.value = '';
            currentCourseIDForSubItems = null;
            courseModalLabel.textContent = 'Thêm Khóa học';

            difficultySelect.value = "";
            languageSelect.value = "";
            Array.from(instructorsSelect.options).forEach(option => option.selected = false);
            Array.from(categoriesSelect.options).forEach(option => option.selected = false);

            imagePreview.src = '#';
            imagePreview.style.display = 'none';
            imagePreview.removeAttribute('data-existing-url');
            imageInput.value = '';

            resetSubItemsUI();
            objectivesListDiv.innerHTML = '<div class="text-center text-muted p-2">Lưu khóa học để thêm mục tiêu.</div>';
            requirementsListDiv.innerHTML = '<div class="text-center text-muted p-2">Lưu khóa học để thêm yêu cầu.</div>';
            addObjectiveBtn.disabled = true;
            addRequirementBtn.disabled = true;

            courseModal.show();
        });
    });

    async function handleEditCourse(event) {
        const btn = event.currentTarget;
        const dataString = btn.getAttribute('data-course');
        if (!dataString) {
            console.error('data-course attribute is missing or empty');
            showAlert('Lỗi: Không thể tải dữ liệu khóa học.', 'danger');
            return;
        }
        try {
            const data = JSON.parse(dataString);
            form.reset();
            actInput.value = 'update';
            idInput.value = data.courseID || '';
            currentCourseIDForSubItems = data.courseID;
            titleInput.value = data.title || '';
            priceInput.value = data.price || '0';
            difficultySelect.value = data.difficulty || '';
            // console.log(data.difficulty);
            languageSelect.value = data.language || '';
            descriptionInput.value = data.description || '';
            courseModalLabel.textContent = 'Sửa Khóa học';

            Array.from(instructorsSelect.options).forEach(opt => opt.selected = false);
            if (data.instructors && Array.isArray(data.instructors)) {
                const selectedInstructorIDs = data.instructors.map(instr => instr.instructorID.toString());
                Array.from(instructorsSelect.options).forEach(option => {
                    if (selectedInstructorIDs.includes(option.value)) {
                        option.selected = true;
                    }
                });
            }

            Array.from(categoriesSelect.options).forEach(opt => opt.selected = false);
            if (data.categories && Array.isArray(data.categories)) {
                const selectedCategoryIDs = data.categories.map(cat => (cat.id || cat.categoryID).toString());
                Array.from(categoriesSelect.options).forEach(option => {
                    if (selectedCategoryIDs.includes(option.value)) {
                        option.selected = true;
                    }
                });
            }

            imageInput.value = '';
            imagePreview.src = '#';
            imagePreview.style.display = 'none';
            imagePreview.removeAttribute('data-existing-url');

            if (currentCourseIDForSubItems) {
                const imgResponse = await fetchApi(`course_image_api.php?courseID=${currentCourseIDForSubItems}&isPrimary=true`);
                if (imgResponse.success && imgResponse.data && Array.isArray(imgResponse.data) && imgResponse.data.length > 0) {
                    const imagePathFromServer = imgResponse.data[0].imagePath;
                    if (imagePathFromServer) {
                        const imageName = imagePathFromServer.split('/').pop();
                        const imageUrl = `${PROJECT_BAS../../backend/Controller/Form/${IMAGE_SERVING_SCRIPT_NAME}?act=serve_image&course_id=${currentCourseIDForSubItems}&image=${encodeURIComponent(imageName)}`;
                        imagePreview.src = imageUrl;
                        imagePreview.style.display = 'block';
                        imagePreview.dataset.existingUrl = imageUrl;
                    }
                } else if (imgResponse.success && imgResponse.data && imgResponse.data.length === 0) {
                    const anyImgResponse = await fetchApi(`course_image_api.php?courseID=${currentCourseIDForSubItems}`);
                    if (anyImgResponse.success && anyImgResponse.data && Array.isArray(anyImgResponse.data) && anyImgResponse.data.length > 0) {
                        const imagePathFromServer = anyImgResponse.data[0].imagePath;
                        if (imagePathFromServer) {
                            const imageName = imagePathFromServer.split('/').pop();
                            const imageUrl = `${PROJECT_BAS../../backend/Controller/Form/${IMAGE_SERVING_SCRIPT_NAME}?act=serve_image&course_id=${currentCourseIDForSubItems}&image=${encodeURIComponent(imageName)}`;
                            imagePreview.src = imageUrl;
                            imagePreview.style.display = 'block';
                            imagePreview.dataset.existingUrl = imageUrl;
                        }
                    }
                }
            }

            addObjectiveBtn.disabled = !currentCourseIDForSubItems;
            addRequirementBtn.disabled = !currentCourseIDForSubItems;

            resetSubItemsUI();
            if (currentCourseIDForSubItems) {
                loadObjectives(currentCourseIDForSubItems);
                loadRequirements(currentCourseIDForSubItems);
            }
            courseModal.show();
        } catch (e) {
            console.error('Error parsing data-course JSON or setting up modal:', e);
            showAlert('Lỗi: Không thể xử lý dữ liệu khóa học để sửa. ' + e.message, 'danger');
        }
    }

    function handleDeleteCourse(event) {
        const btn = event.currentTarget;
        event.preventDefault();
        if (!window.confirm('Bạn có chắc muốn xóa khóa học này? Hành động này không thể hoàn tác.')) return;
        const courseIdToDelete = btn.getAttribute('data-id');
        if (!courseIdToDelete) {
            showAlert('Lỗi: Không tìm thấy ID khóa học để xóa.', 'danger');
            return;
        }

        window.location.href = `../../backend/Controller/Form/c_course_management.php?act=delete&courseID=${encodeURIComponent(courseIdToDelete)}`;
    }

    courseModalElement.addEventListener('hidden.bs.modal', function () {
        form.reset();
        actInput.value = 'create';
        idInput.value = '';
        currentCourseIDForSubItems = null;
        difficultySelect.value = "";
        languageSelect.value = "";
        Array.from(instructorsSelect.options).forEach(option => option.selected = false);
        Array.from(categoriesSelect.options).forEach(option => option.selected = false);
        imagePreview.src = '#';
        imagePreview.style.display = 'none';
        imagePreview.removeAttribute('data-existing-url');
        imageInput.value = '';
        resetSubItemsUI();
        courseModalLabel.textContent = 'Thêm Khóa học';
        addObjectiveBtn.disabled = true;
        addRequirementBtn.disabled = true;
    });

    document.getElementById('applyFiltersBtn').addEventListener('click', () => {
        currentSearchTerm = document.getElementById('searchInput').value.trim();
        currentDifficultyFilter = document.getElementById('difficultyFilter').value;
        currentLanguageFilter = document.getElementById('languageFilter').value;
        loadCourses(1); // Reset to page 1 when filters change
    });

    document.getElementById('searchInput').addEventListener('keyup', (event) => {
        if (event.key === 'Enter') {
            currentSearchTerm = document.getElementById('searchInput').value.trim();
            document.getElementById('applyFiltersBtn').click();
        }
    });


    document.getElementById('prevPageLink').addEventListener('click', (e) => {
        e.preventDefault();
        if (currentPage > 1) {
            loadCourses(currentPage - 1);
        }
    });

    document.getElementById('nextPageLink').addEventListener('click', (e) => {
        e.preventDefault();
        loadCourses(currentPage + 1);
    });

    // Initial load
    document.addEventListener('DOMContentLoaded', () => {
        loadCourses(1);
    });
</script>
</body>

</html>