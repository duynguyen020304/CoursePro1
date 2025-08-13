<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$h_protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http');
$h_host = $_SERVER['HTTP_HOST'];
$h_script_path = $_SERVER['SCRIPT_NAME'];
$h_app_root_path_relative = '';

$h_known_app_subdir_markers = ['/admin/', '/api/', '/includes/', '/controller/', '/view/', '/template/'];
$h_found_marker = false;
foreach ($h_known_app_subdir_markers as $h_marker) {
    $h_pos = strpos($h_script_path, $h_marker);
    if ($h_pos !== false) {
        $h_app_root_path_relative = substr($h_script_path, 0, $h_pos);
        $h_found_marker = true;
        break;
    }
}

if (!$h_found_marker) {
    $h_app_root_path_relative = dirname($h_script_path);
    if (($h_app_root_path_relative === '/' || $h_app_root_path_relative === '\\')) {
        if (substr_count(ltrim($h_script_path, '/'), '/') == 0) {
            $h_app_root_path_relative = '';
        }
    }
}

if ($h_app_root_path_relative !== '/' && $h_app_root_path_relative !== '') {
    $h_app_root_path_relative = rtrim($h_app_root_path_relative, '/');
}

if (!defined('APP_ROOT_PATH_RELATIVE_HEADER')) {
    define('APP_ROOT_PATH_RELATIVE_HEADER', $h_app_root_path_relative);
}
if (!defined('API_BASE_HEADER')) {
    define('API_BASE_HEADER', $h_protocol . '://' . $h_host . APP_ROOT_PATH_RELATIVE_HEADER . '/api');
}

if (!function_exists('callApiForHeader')) {
    function callApiForHeader(string $endpoint, string $method = 'GET', array $payload = []): array
    {
        $url = API_BASE_HEADER . '/' . ltrim($endpoint, '/');
        $methodUpper = strtoupper($method);

        if ($methodUpper === 'GET' && !empty($payload)) {
            $url .= '?' . http_build_query($payload);
        }

        $headers_arr = [
            "Content-Type: application/json; charset=utf-8",
            "Accept: application/json"
        ];

        $token = $_SESSION['user']['token'] ?? null;
        if ($token) {
            $headers_arr[] = "Authorization: Bearer " . $token;
        }
        $headers_str = implode("\r\n", $headers_arr);

        $options = [
            'http' => [
                'method'        => $methodUpper,
                'header'        => $headers_str,
                'ignore_errors' => true,
                'timeout'       => 10
            ]
        ];

        if ($methodUpper !== 'GET' && !empty($payload)) {
            $options['http']['content'] = json_encode($payload);
        } elseif (in_array($methodUpper, ['POST', 'PUT']) && empty($payload)) {
            $options['http']['content'] = '{}';
        }

        $context  = stream_context_create($options);
        $response = @file_get_contents($url, false, $context);

        $status_code = 0;
        if (isset($http_response_header[0]) && preg_match('{HTTP/\S*\s(\d{3})}', $http_response_header[0], $match)) {
            $status_code = intval($match[1]);
        }

        if ($response === false) {
            return ['success' => false, 'message' => 'API connection failed for header. Could not reach ' . $url, 'http_status_code' => $status_code, 'data' => null];
        }

        $result = json_decode($response, true);

        if ($result === null && json_last_error() !== JSON_ERROR_NONE && !empty($response)) {
            return ['success' => false, 'message' => 'Invalid API response (not JSON) for header. Error: ' . json_last_error_msg(), 'http_status_code' => $status_code, 'data' => null, 'raw_response' => substr($response, 0, 500)];
        }

        if (!is_array($result)) {
            $result = [];
        }

        $result['http_status_code'] = $status_code;
        if (!isset($result['success'])) {
            $result['success'] = ($status_code >= 200 && $status_code < 300);
        }
        if (!isset($result['data'])) {
            $result['data'] = null;
        }
        if (!isset($result['message']) && !$result['success']) {
            $result['message'] = 'API request failed with HTTP status ' . $status_code;
        } elseif (!isset($result['message']) && $result['success']) {
            $result['message'] = 'API request successful.';
        }
        return $result;
    }
}

$cart_item_count = 0;
if (isset($_SESSION['user']['token']) && isset($_SESSION['user']['userID'])) {
    $cart_api_response = callApiForHeader('cart_api.php', 'GET', ['userID' => $_SESSION['user']['userID'], 'isActive' => 'true']);
    $current_cart_id = null;

    if (isset($cart_api_response['success']) && $cart_api_response['success']) {
        if (isset($cart_api_response['data'])) {
            if (isset($cart_api_response['data']['cartID'])) {
                $current_cart_id = $cart_api_response['data']['cartID'];
            } elseif (is_array($cart_api_response['data']) && !empty($cart_api_response['data']) && isset($cart_api_response['data'][0]['cartID'])) {
                $current_cart_id = $cart_api_response['data'][0]['cartID'];
            }
        }
    }

    if ($current_cart_id) {
        $cart_items_response = callApiForHeader('cart_item_api.php', 'GET', ['cartID' => $current_cart_id]);
        if (isset($cart_items_response['success']) && $cart_items_response['success'] && isset($cart_items_response['data']) && is_array($cart_items_response['data'])) {
            $cart_item_count = count($cart_items_response['data']);
        }
    }
}
?>

<style>
    .search-form .search-input-wrapper {
        position: relative;
        width: 100%;
        max-width: 600px;
    }

    .search-form .search-input.form-control {
        padding-right: 3rem;
        border-radius: 999px;
        border: 1px solid #1c1d1f;
        height: 46px;
        padding-left: 1.5rem;
    }

    .search-form .search-input.form-control:focus {
        border-color: #1c1d1f;
        box-shadow: 0 0 0 0.2rem rgba(28, 29, 31, .25);
    }


    .search-form .search-icon-inside-btn {
        position: absolute;
        right: 0;
        top: 0;
        height: 100%;
        width: 3rem;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: transparent;
        border: none;
        padding: 0;
        margin: 0;
        color: #1c1d1f;
        cursor: pointer;
        z-index: 4;
        border-top-right-radius: 999px;
        border-bottom-right-radius: 999px;
    }

    .search-form .search-icon-inside-btn:hover,
    .search-form .search-icon-inside-btn:focus {
        color: #0056b3;
        background-color: transparent;
        border: none;
        outline: none;
        box-shadow: none;
    }

    .search-form .search-icon-inside-btn svg {
        width: 18px;
        height: 18px;
    }

    .search-suggestions-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background-color: #fff;
        border: 1px solid #d1d7dc;
        border-top: none;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        z-index: 1050;
        max-height: 400px;
        overflow-y: auto;
        display: none;
        border-bottom-left-radius: 4px;
        border-bottom-right-radius: 4px;
    }

    .search-suggestions-dropdown .suggestion-item {
        display: flex;
        align-items: center;
        padding: 10px 15px;
        cursor: pointer;
        text-decoration: none;
        color: #1c1d1f;
        border-bottom: 1px solid #f0f0f0;
    }

    .search-suggestions-dropdown .suggestion-item:last-child {
        border-bottom: none;
    }


    .search-suggestions-dropdown .suggestion-item:hover {
        background-color: #f7f9fa;
    }

    .search-suggestions-dropdown .suggestion-item-image {
        width: 60px;
        height: 40px;
        object-fit: cover;
        margin-right: 12px;
        border: 1px solid #e0e0e0;
        background-color: #f0f0f0;
    }

    .search-suggestions-dropdown .suggestion-item-details {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .search-suggestions-dropdown .suggestion-item-title {
        font-weight: bold;
        font-size: 0.9rem;
        color: #1c1d1f;
        margin: 0;
        line-height: 1.3;
    }

    .search-suggestions-dropdown .suggestion-item-instructor {
        font-size: 0.8rem;
        color: #505763;
        margin: 0;
        line-height: 1.3;
    }

    .search-suggestions-dropdown .suggestion-item-loading,
    .search-suggestions-dropdown .suggestion-item-no-results {
        padding: 15px;
        text-align: center;
        color: #505763;
        font-style: italic;
    }

    .navbar-nav.right-nav {
        flex-shrink: 0;
    }

    .custom-navbar .search-form {
        flex-grow: 1;
        margin-left: 1rem !important;
        margin-right: 1rem !important;
        display: flex;
        justify-content: center;
    }
</style>

<nav class="navbar navbar-expand-lg custom-navbar">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?php echo APP_ROOT_PATH_RELATIVE_HEADER; ?>/home.php">Course Online</a>
        <a class="nav-link category-link" href="#" id="categoryMenuBtn">Danh mục</a>
        <div id="categoryDropdownMenu" class="category-dropdown-menu">
        </div>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <form class="form-inline my-2 my-lg-0 search-form mx-auto" action="<?php echo APP_ROOT_PATH_RELATIVE_HEADER; ?>/search-results.php" method="GET" id="courseSearchForm">
                <div class="search-input-wrapper">
                    <input class="form-control search-input" type="search" name="query" id="searchInput"
                        placeholder="Tìm kiếm khóa học..." aria-label="Search Courses" autocomplete="off">
                    <button type="submit" class="search-icon-inside-btn" aria-label="Search button">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                            class="bi bi-search" viewBox="0 0 16 16">
                            <path
                                d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0" />
                        </svg>
                    </button>
                    <div id="searchSuggestionsDropdown" class="search-suggestions-dropdown">
                    </div>
                </div>
            </form>

            <ul class="navbar-nav right-nav align-items-center">
                <li class="nav-item">
                    <a class="nav-link position-relative" href="<?php echo APP_ROOT_PATH_RELATIVE_HEADER; ?>/cart.php" aria-label="Giỏ hàng">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor"
                            class="bi bi-cart" viewBox="0 0 16 16">
                            <path
                                d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .491.592l-1.5 8A.5.5 0 0 1 13 12H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5M3.102 4l1.313 7h8.17l1.313-7H3.102zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4m7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4m-7 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2m7 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2" />
                        </svg>
                        <?php if ($cart_item_count > 0): ?>
                            <span class="badge bg-danger rounded-pill position-absolute top-0 start-100 translate-middle" style="font-size: 0.6em; padding: 0.2em 0.35em; line-height: 1;">
                                <?php echo $cart_item_count; ?>
                                <span class="visually-hidden">sản phẩm trong giỏ</span>
                            </span>
                        <?php endif; ?>
                    </a>
                </li>
                <?php
                if (isset($_SESSION['user']) && is_array($_SESSION['user']) && isset($_SESSION['user']['firstName']) && isset($_SESSION['user']['lastName']) && isset($_SESSION['user']['email'])) {
                    $user = $_SESSION['user'];
                    $avatar = '';
                    $firstNameInitial = !empty(trim($user['firstName'])) ? mb_substr(trim($user['firstName']), 0, 1, 'UTF-8') : '';
                    $lastNameInitial  = !empty(trim($user['lastName'])) ? mb_substr(trim($user['lastName']),  0, 1, 'UTF-8') : '';

                    if ($firstNameInitial !== '' || $lastNameInitial !== '') {
                        $avatar = strtoupper($firstNameInitial . $lastNameInitial);
                    } else {
                        $emailPrefix = explode('@', $user['email'])[0];
                        $avatar = strtoupper(mb_substr($emailPrefix, 0, 2, 'UTF-8'));
                        if (empty($avatar)) $avatar = '??';
                    }
                ?>
                    <li class="nav-item dropdown user-avatar-nav">
                        <a class="nav-link avatar-btn" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="avatar-circle"><?php echo htmlspecialchars($avatar); ?></span>
                            <span class="avatar-dot"></span> </a>
                        <ul class="dropdown-menu user-dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li>
                                <div class="dropdown-header text-center p-3">
                                    <span class="avatar-circle-big mb-2"><?php echo htmlspecialchars($avatar); ?></span><br>
                                    <b class="d-block"><?php echo htmlspecialchars(trim($user['firstName'] . ' ' . $user['lastName'])); ?></b>
                                    <span class="text-muted small d-block"><?php echo htmlspecialchars($user['email']); ?></span>
                                </div>
                            </li>
                            <li>
                                <hr class="dropdown-divider my-1">
                            </li>
                            <li><a class="dropdown-item" href="<?php echo APP_ROOT_PATH_RELATIVE_HEADER; ?>/user.php"><i class="fas fa-book-open me-2"></i>Khóa học của tôi</a></li>
                            <li><a class="dropdown-item" href="<?php echo APP_ROOT_PATH_RELATIVE_HEADER; ?>/cart.php"><i class="fas fa-shopping-cart me-2"></i>Giỏ hàng
                                        <?php if ($cart_item_count > 0): ?>
                                            <span class="badge rounded-pill bg-primary ms-1"><?php echo $cart_item_count; ?></span>
                                        <?php endif; ?>
                                    </a>
                            </li>
                            <li><a class="dropdown-item" href="#"><i class="fas fa-heart me-2"></i>Danh sách yêu thích</a></li>
                            <li>
                                <hr class="dropdown-divider my-1">
                            </li>
                            <li><a class="dropdown-item" href="#"><i class="fas fa-bell me-2"></i>Thông báo</a></li>
                            <li>
                                <hr class="dropdown-divider my-1">
                            </li>
                            <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i>Cài đặt tài khoản</a></li>
                            <li><a class="dropdown-item" href="<?php echo APP_ROOT_PATH_RELATIVE_HEADER; ?>/purchase-history.php"><i class="fas fa-history me-2"></i>Lịch sử mua hàng</a></li>
                            <li>
                                <hr class="dropdown-divider my-1">
                            </li>
                            <li>
                                <?php
                                $role_href = APP_ROOT_PATH_RELATIVE_HEADER . "/edit-profile.php";
                                if (isset($_SESSION['user']['role'])) {
                                    if ($_SESSION['user']['role'] == 'admin') {
                                        $role_href = APP_ROOT_PATH_RELATIVE_HEADER . "/admin/dashboard.php";
                                    } elseif ($_SESSION['user']['role'] == 'instructor') {
                                        $role_href = APP_ROOT_PATH_RELATIVE_HEADER . "/instructor/dashboard.php";
                                    }
                                }
                                ?>
                                <a class="dropdown-item" href="<?php echo htmlspecialchars($role_href); ?>"><i class="fas fa-user-circle me-2"></i>Hồ sơ</a>
                            </li>
                            <li>
                                <hr class="dropdown-divider my-1">
                            </li>
                            <li><a class="dropdown-item text-danger" href="<?php echo APP_ROOT_PATH_RELATIVE_HEADER; ?>/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Đăng xuất</a></li>
                        </ul>
                    </li>
                <?php } else { ?>
                    <li class="nav-item">
                        <a class="nav-link btn btn-outline-primary me-2 btn-sm" href="<?php echo APP_ROOT_PATH_RELATIVE_HEADER; ?>/signin.php">Đăng nhập</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-primary btn-sm" href="<?php echo APP_ROOT_PATH_RELATIVE_HEADER; ?>/signup.php">Đăng ký</a>
                    </li>
                <?php } ?>
            </ul>
        </div>
    </div>
</nav>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const categoryBtn = document.getElementById("categoryMenuBtn");
        const categoryDropdown = document.getElementById("categoryDropdownMenu");
        let categoriesLoaded = false;
        const appRootPath = "<?php echo APP_ROOT_PATH_RELATIVE_HEADER; ?>";

        function buildTree(categories, parentId = null) {
            let html = '<ul class="cat-tree-menu">';
            const directChildren = categories.filter(cat => {
                const catParentId = String(cat.parent_id);
                const targetParentId = String(parentId);
                return catParentId === targetParentId || (parentId === null && (cat.parent_id == null || cat.parent_id == 0 || cat.parent_id == "0"));
            });

            directChildren.forEach(cat => {
                const children = categories.filter(c => String(c.parent_id) === String(cat.id));
                const hasChildren = children.length > 0;
                html += `<li class="cat-tree-item${hasChildren ? ' has-children' : ''}">`;
                html += `<a href="${appRootPath}/course-category.php?categoryID=${cat.id}" class="cat-tree-link">${cat.name}${hasChildren ? '<span class="cat-arrow">&#8250;</span>' : ''}</a>`;
                if (hasChildren) {
                    html += buildTree(categories, cat.id);
                }
                html += `</li>`;
            });
            html += '</ul>';
            return html;
        }

        function alignCategoryDropdown() {
            if (!categoryBtn || !categoryDropdown) return;
            const rect = categoryBtn.getBoundingClientRect();
            categoryDropdown.style.position = "absolute";
            categoryDropdown.style.left = rect.left + window.scrollX + 'px';
            categoryDropdown.style.top = rect.bottom + window.scrollY + 'px';
            categoryDropdown.style.minWidth = Math.max(categoryBtn.offsetWidth, 250) + 'px';
            categoryDropdown.style.zIndex = 1050;
        }

        let categoryTimeoutEnter = null;
        let categoryTimeoutLeave = null;

        if (categoryBtn && categoryDropdown) {
            const showDropdown = () => {
                if (categoryTimeoutLeave) clearTimeout(categoryTimeoutLeave);
                alignCategoryDropdown();
                categoryDropdown.style.display = "block";

                if (!categoriesLoaded) {
                    categoryDropdown.innerHTML = '<li class="p-2 text-muted">Đang tải...</li>';
                    fetch(`${appRootPath}/api/category_api.php?isDisplay=1`)
                        .then(res => {
                            if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);
                            return res.json();
                        })
                        .then(data => {
                            if (data && data.success && data.data && data.data.length > 0) {
                                categoryDropdown.innerHTML = buildTree(data.data, null);
                            } else {
                                categoryDropdown.innerHTML = `<li class='p-2 text-muted'>${data.message || "Không có danh mục nào."}</li>`;
                            }
                            categoriesLoaded = true;
                        }).catch(error => {
                            console.error("Error fetching categories:", error);
                            categoryDropdown.innerHTML = `<li class='p-2 text-danger'>Lỗi tải danh mục: ${error.message}.</li>`;
                            categoriesLoaded = true;
                        });
                }
            };

            const hideDropdown = () => {
                categoryTimeoutLeave = setTimeout(() => {
                    if (!categoryDropdown.matches(":hover")) {
                        categoryDropdown.style.display = "none";
                    }
                }, 200);
            };

            categoryBtn.addEventListener("mouseenter", () => {
                if (categoryTimeoutEnter) clearTimeout(categoryTimeoutEnter);
                categoryTimeoutEnter = setTimeout(showDropdown, 50);
            });
            categoryBtn.addEventListener("mouseleave", hideDropdown);

            categoryDropdown.addEventListener("mouseenter", () => {
                if (categoryTimeoutLeave) clearTimeout(categoryTimeoutLeave);
            });
            categoryDropdown.addEventListener("mouseleave", hideDropdown);

            document.addEventListener("click", function(e) {
                if (categoryDropdown.style.display === 'block' && !categoryBtn.contains(e.target) && !categoryDropdown.contains(e.target)) {
                    categoryDropdown.style.display = "none";
                }
            });
            window.addEventListener('resize', alignCategoryDropdown);
        }

        var userDropdownTrigger = document.getElementById("userDropdown");
        if (userDropdownTrigger) {
            userDropdownTrigger.addEventListener("click", function(e) {
                e.preventDefault();
                var parentElement = this.closest('.user-avatar-nav');
                var menu = this.nextElementSibling;

                if (parentElement && menu) {
                    const isShown = parentElement.classList.toggle("show");
                    menu.classList.toggle("show", isShown);
                    this.setAttribute('aria-expanded', isShown.toString());
                }
            });
        }

        document.addEventListener("click", function(e) {
            var avatarNav = document.querySelector(".user-avatar-nav.show");
            if (avatarNav && !avatarNav.contains(e.target)) {
                var menu = avatarNav.querySelector('.dropdown-menu');
                var trigger = avatarNav.querySelector('#userDropdown');

                avatarNav.classList.remove("show");
                if (menu) menu.classList.remove("show");
                if (trigger) trigger.setAttribute('aria-expanded', 'false');
            }
        });

        const searchInput = document.getElementById('searchInput');
        const suggestionsDropdown = document.getElementById('searchSuggestionsDropdown');
        const courseSearchForm = document.getElementById('courseSearchForm');
        let debounceTimer;

        if (searchInput && suggestionsDropdown && courseSearchForm) {
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.trim();
                clearTimeout(debounceTimer);

                if (searchTerm.length === 0) {
                    suggestionsDropdown.style.display = 'none';
                    suggestionsDropdown.innerHTML = '';
                    return;
                }

                suggestionsDropdown.style.display = 'block';
                suggestionsDropdown.innerHTML = '<div class="suggestion-item-loading">Đang tìm kiếm...</div>';

                debounceTimer = setTimeout(() => {
                    fetchCourseSuggestions(searchTerm);
                }, 300);
            });

            searchInput.addEventListener('focus', function() {
                const searchTerm = this.value.trim();
                if (searchTerm.length > 0 && suggestionsDropdown.children.length > 0 && suggestionsDropdown.innerHTML.trim() !== '' && !suggestionsDropdown.querySelector('.suggestion-item-loading')) {
                    suggestionsDropdown.style.display = 'block';
                }
            });


            async function fetchCourseSuggestions(query) {
                const searchApiUrl = `${appRootPath}/api/search_course_api.php?title=${encodeURIComponent(query)}`;
                try {
                    const response = await fetch(searchApiUrl);
                    if (!response.ok) {
                        if (response.headers.get("content-type")?.includes("application/json")) {
                            const errorData = await response.json();
                            console.error('API error when fetching suggestions:', errorData);
                            displaySuggestionsError(`Lỗi API: ${errorData.message || response.statusText}`);
                            return;
                        }
                        throw new Error(`Lỗi mạng hoặc máy chủ: ${response.statusText} (HTTP ${response.status})`);
                    }
                    const courses = await response.json();
                    renderSuggestions(courses);
                } catch (error) {
                    console.error('Lỗi khi tìm kiếm khóa học:', error);
                    displaySuggestionsError('Không thể tải gợi ý. Vui lòng thử lại.');
                }
            }

            function renderSuggestions(courses) {
                suggestionsDropdown.innerHTML = '';

                if (!courses || courses.length === 0) {
                    suggestionsDropdown.innerHTML = '<div class="suggestion-item-no-results">Không tìm thấy khóa học nào.</div>';
                    suggestionsDropdown.style.display = 'block';
                    return;
                }

                courses.forEach(course => {
                    const item = document.createElement('a');
                    item.classList.add('suggestion-item');
                    item.href = `${appRootPath}/course-detail.php?courseID=${course.courseID}`;

                    const imageContainer = document.createElement('div');
                    imageContainer.classList.add('suggestion-item-image-container');

                    const img = document.createElement('img');
                    img.classList.add('suggestion-item-image');
                    if (course.images && course.images.length > 0 && course.images[0]) {
                        console.log(course.images)
                        img.src = `${appRootPath}/controller/c_file_loader.php?act=serve_image&course_id=${course.courseID}&image=${encodeURIComponent(course.images[0]['imagePath'])}`;
                        img.alt = course.title;
                    } else {
                        img.src = `https://placehold.co/60x40/e0e0e0/777?text=N/A`;
                        img.alt = "Không có hình ảnh";
                    }
                    img.onerror = function() {
                        this.src = `https://placehold.co/60x40/e0e0e0/999?text=Error`;
                        this.alt = "Lỗi tải ảnh";
                    };

                    item.appendChild(img);

                    const detailsDiv = document.createElement('div');
                    detailsDiv.classList.add('suggestion-item-details');

                    const titleSpan = document.createElement('span');
                    titleSpan.classList.add('suggestion-item-title');
                    titleSpan.textContent = course.title;
                    detailsDiv.appendChild(titleSpan);

                    if (course.instructors && course.instructors.length > 0) {
                        const instructor = course.instructors[0];
                        const instructorSpan = document.createElement('span');
                        instructorSpan.classList.add('suggestion-item-instructor');
                        let instructorName = `${instructor.firstName || ''} ${instructor.lastName || ''}`.trim();
                        if (instructorName) {
                            instructorSpan.textContent = `Course by ${instructorName}`;
                            detailsDiv.appendChild(instructorSpan);
                        }
                    }

                    item.appendChild(detailsDiv);
                    suggestionsDropdown.appendChild(item);
                });
                suggestionsDropdown.style.display = 'block';
            }

            function displaySuggestionsError(message) {
                suggestionsDropdown.innerHTML = `<div class="suggestion-item-no-results">${message}</div>`;
                suggestionsDropdown.style.display = 'block';
            }

            document.addEventListener('click', function(event) {
                if (!courseSearchForm.contains(event.target) && suggestionsDropdown.style.display === 'block') {
                    suggestionsDropdown.style.display = 'none';
                }
            });

            searchInput.addEventListener('keydown', function(event) {
                if (event.key === "Escape") {
                    suggestionsDropdown.style.display = 'none';
                }
            });
        }
    });
</script>
