<?php
// template/header.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// --- Logic để xác định $app_root_path_relative và API_BASE ---
$h_protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http');
$h_host = $_SERVER['HTTP_HOST'];
$h_script_path = $_SERVER['SCRIPT_NAME'];
$h_path_parts = explode('/', ltrim($h_script_path, '/'));
$h_app_root_path_relative = '';

if (count($h_path_parts) > 0 && $h_path_parts[0] !== basename($h_script_path)) {
    $h_app_root_path_relative = '/' . $h_path_parts[0];
}
$h_known_app_subdir_markers = ['/admin/', '/api/', '/includes/'];
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
    if (($h_app_root_path_relative === '/' || $h_app_root_path_relative === '\\') && $h_script_path !== '/') {
        $h_app_root_path_relative = '';
    } elseif ($h_app_root_path_relative === '.' && ltrim($h_script_path, '/') !== basename($h_script_path)) {
        $h_app_root_path_relative = '';
    } elseif ($h_app_root_path_relative === '.') {
        $h_app_root_path_relative = '';
    }
}
if ($h_app_root_path_relative !== '/' && $h_app_root_path_relative !== '' && substr($h_app_root_path_relative, -1) === '/') {
    $h_app_root_path_relative = rtrim($h_app_root_path_relative, '/');
}
if ($h_app_root_path_relative === '/' && $h_script_path === '/') {
    $h_app_root_path_relative = '';
}
if (!defined('APP_ROOT_PATH_RELATIVE_HEADER')) {
    define('APP_ROOT_PATH_RELATIVE_HEADER', $h_app_root_path_relative);
}
if (!defined('API_BASE_HEADER')) {
    define('API_BASE_HEADER', $h_protocol . '://' . $h_host . APP_ROOT_PATH_RELATIVE_HEADER . '/api');
}
// --- Kết thúc logic xác định đường dẫn ---

// --- Hàm callApi cho header (phiên bản rút gọn nếu chỉ GET) ---
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
        if (isset($http_response_header[0]) && preg_match('{HTTP\/\S*\s(\d{3})}', $http_response_header[0], $match)) {
            $status_code = intval($match[1]);
        }

        if ($response === false) {
            return ['success' => false, 'message' => 'API connection failed for header.', 'http_status_code' => $status_code, 'data' => null];
        }

        $result = json_decode($response, true);

        if ($result === null && json_last_error() !== JSON_ERROR_NONE && !empty($response)) {
            return ['success' => false, 'message' => 'Invalid API response (not JSON) for header. Error: ' . json_last_error_msg(), 'http_status_code' => $status_code, 'data' => null, 'raw_response' => $response];
        }

        if (!is_array($result)) {
            $result = [];
        }

        $result['http_status_code'] = $status_code;
        if (!isset($result['success'])) {
            $result['success'] = ($status_code >= 200 && $status_code < 300);
        }
        return $result;
    }
}
// --- Kết thúc hàm callApi cho header ---

// --- Lấy số lượng giỏ hàng ---
$cart_item_count = 0;
if (isset($_SESSION['user']['token']) && isset($_SESSION['user']['userID'])) {
    $cart_api_response = callApiForHeader('cart_api.php', 'GET');
    $current_cart_id = null;

    if (isset($cart_api_response['success']) && $cart_api_response['success']) {
        if (isset($cart_api_response['cartID'])) {
            $current_cart_id = $cart_api_response['cartID'];
        } elseif (isset($cart_api_response['data']['cartID'])) {
            $current_cart_id = $cart_api_response['data']['cartID'];
        }
    }

    if ($current_cart_id) {
        $cart_items_response = callApiForHeader('cart_item_api.php', 'GET', ['cartID' => $current_cart_id]);
        if (isset($cart_items_response['status']) && $cart_items_response['status'] === 'success' && isset($cart_items_response['data']) && is_array($cart_items_response['data'])) {
            $cart_item_count = count($cart_items_response['data']);
        }
    }
}
// --- Kết thúc lấy số lượng giỏ hàng ---
?>
<nav class="navbar navbar-expand-lg custom-navbar">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?php echo APP_ROOT_PATH_RELATIVE_HEADER; ?>/home.php">Course Online</a>
        <a class="nav-link category-link" href="#" id="categoryMenuBtn">Category</a>
        <div id="categoryDropdownMenu" class="category-dropdown-menu"></div>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <form class="form-inline my-2 my-lg-0 search-form mx-auto">
                <div class="search-input-wrapper">
                    <input class="form-control search-input" type="search"
                        placeholder="Tìm kiếm khóa học, kỹ năng, chủ đề hoặc giảng viên" aria-label="Search">
                    <div class="search-icon-inside">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                            class="bi bi-search" viewBox="0 0 16 16">
                            <path
                                d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0" />
                        </svg>
                    </div>
                </div>
            </form>

            <ul class="navbar-nav right-nav">
                <li class="nav-item">
                    <a class="nav-link position-relative" href="<?php echo APP_ROOT_PATH_RELATIVE_HEADER; ?>/cart.php">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                            class="bi bi-cart" viewBox="0 0 16 16">
                            <path
                                d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .491.592l-1.5 8A.5.5 0 0 1 13 12H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5M3.102 4l1.313 7h8.17l1.313-7H3.102zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4m7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4m-7 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2m7 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2" />
                        </svg>
                        <?php if ($cart_item_count > 0): ?>
                            <span class="badge bg-danger rounded-pill position-absolute top-0 start-100 translate-middle" style="font-size: 0.65em; padding: 0.2em 0.4em;">
                                <?php echo $cart_item_count; ?>
                                <span class="visually-hidden">items in cart</span>
                            </span>
                        <?php endif; ?>
                    </a>
                </li>
                <?php
                if (isset($_SESSION['user']) && is_array($_SESSION['user']) && isset($_SESSION['user']['firstName']) && isset($_SESSION['user']['lastName']) && isset($_SESSION['user']['email'])) {
                    $user = $_SESSION['user'];
                    $avatar = '';
                    $firstNameInitial = mb_substr(trim($user['firstName']), 0, 1, 'UTF-8');
                    $lastNameInitial  = mb_substr(trim($user['lastName']),  0, 1, 'UTF-8');
                    if ($firstNameInitial !== '' && $lastNameInitial !== '') {
                        $avatar = strtoupper($firstNameInitial . $lastNameInitial);
                    } else {
                        if (!empty($user['name'])) {
                            $parts = explode(' ', trim($user['name']));
                            if (count($parts) >= 2) {
                                $avatar = strtoupper(
                                    mb_substr($parts[0], 0, 1, 'UTF-8')
                                        . mb_substr(end($parts), 0, 1, 'UTF-8')
                                );
                            } elseif (!empty($parts[0])) {
                                $avatar = strtoupper(mb_substr($parts[0], 0, 2, 'UTF-8'));
                            }
                        }
                        if ($avatar === '') {
                            $avatar = '??';
                        }
                    }
                ?>
                    <li class="nav-item dropdown user-avatar-nav">
                        <a class="nav-link avatar-btn" href="#" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="avatar-circle"><?php echo htmlspecialchars($avatar); ?></span>
                            <span class="avatar-dot"></span>
                        </a>
                        <ul class="dropdown-menu user-dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li>
                                <div class="dropdown-header text-center">
                                    <span class="avatar-circle-big"><?php echo htmlspecialchars($avatar); ?></span><br>
                                    <b><?php echo htmlspecialchars($user['firstName'] . ' ' . $user['lastName']); ?></b><br>
                                    <span class="text-muted small"><?php echo htmlspecialchars($user['email']); ?></span>
                                </div>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item" href="<?php echo APP_ROOT_PATH_RELATIVE_HEADER; ?>/courses.php">Khóa học</a></li>
                            <li><a class="dropdown-item" href="<?php echo APP_ROOT_PATH_RELATIVE_HEADER; ?>/cart.php">Giỏ hàng
                                    <?php if ($cart_item_count > 0): ?>
                                        <span class="badge rounded-pill bg-primary ms-1"><?php echo $cart_item_count; ?></span>
                                    <?php endif; ?>
                                </a>
                            </li>
                            <li><a class="dropdown-item" href="#">Danh sách yêu thích</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item" href="#">Thông báo</a></li>
                            <li><a class="dropdown-item" href="#">Tin nhắn <span class="badge rounded-pill bg-primary ms-1">3</span></a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item" href="#">Cài đặt</a></li>
                            <li><a class="dropdown-item" href="#">Phương thức thanh toán</a></li>
                            <li><a class="dropdown-item" href="#">Gói đăng ký</a></li>
                            <li><a class="dropdown-item" href="#">Lịch sử mua hàng</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <?php
                                $role_href = APP_ROOT_PATH_RELATIVE_HEADER . "/user.php";
                                if (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] == 'admin') {
                                    $role_href = APP_ROOT_PATH_RELATIVE_HEADER . "/admin/admin.php";
                                }
                                ?>
                                <a class="dropdown-item" href="<?php echo htmlspecialchars($role_href); ?>">Hồ sơ</a>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item text-danger" href="<?php echo APP_ROOT_PATH_RELATIVE_HEADER; ?>/logout.php">Đăng xuất</a></li>
                        </ul>
                    </li>
                <?php } else { ?>
                    <li class="nav-item">
                        <a class="nav-link btn btn-outline-primary me-2" href="<?php echo APP_ROOT_PATH_RELATIVE_HEADER; ?>/signin.php">Sign In</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-primary" href="<?php echo APP_ROOT_PATH_RELATIVE_HEADER; ?>/signup.php">Sign Up</a>
                    </li>
                <?php } ?>
            </ul>
        </div>
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                // Category Dropdown Logic (Giữ nguyên)
                const categoryBtn = document.getElementById("categoryMenuBtn");
                const categoryDropdown = document.getElementById("categoryDropdownMenu"); // Đổi tên biến để tránh nhầm lẫn
                let categoriesLoaded = false; // Đổi tên biến

                function buildTree(categories, parentId = null) {
                    let html = '<ul class="cat-tree-menu">';
                    categories.filter(cat => String(cat.parent_id) === String(parentId)).forEach(cat => {
                        const children = categories.filter(c => String(c.parent_id) === String(cat.id));
                        const hasChildren = children.length > 0;
                        html += `<li class="cat-tree-item${hasChildren ? ' has-children' : ''}">`;
                        html += `<a href="#" class="cat-tree-link">${cat.name}${hasChildren ? '<span class="cat-arrow">&#8250;</span>' : ''}</a>`;
                        if (hasChildren) html += buildTree(categories, cat.id);
                        html += `</li>`;
                    });
                    html += '</ul>';
                    return html;
                }

                function alignCategoryDropdown() { // Đổi tên hàm
                    const rect = categoryBtn.getBoundingClientRect();
                    categoryDropdown.style.position = "absolute";
                    categoryDropdown.style.left = rect.left + 'px';
                    categoryDropdown.style.top = rect.bottom + window.scrollY + 'px';
                    categoryDropdown.style.minWidth = categoryBtn.offsetWidth + 'px';
                    categoryDropdown.style.zIndex = 1050;
                }

                let categoryTimeout = null; // Đổi tên biến
                if (categoryBtn && categoryDropdown) { // Kiểm tra sự tồn tại của element
                    categoryBtn.addEventListener("mouseenter", function() {
                        alignCategoryDropdown();
                        categoryDropdown.style.display = "block";
                        if (!categoriesLoaded) {
                            fetch("<?php echo APP_ROOT_PATH_RELATIVE_HEADER; ?>/api/category_api.php?tree=0")
                                .then(res => res.json())
                                .then(data => {
                                    if (data && data.data) {
                                        categoryDropdown.innerHTML = buildTree(data.data);
                                    } else {
                                        categoryDropdown.innerHTML = "<li>Không tải được danh mục</li>";
                                    }
                                    categoriesLoaded = true;
                                }).catch(error => {
                                    console.error("Error fetching categories:", error);
                                    categoryDropdown.innerHTML = "<li>Lỗi tải danh mục</li>";
                                    categoriesLoaded = true;
                                });
                        }
                    });
                    categoryBtn.addEventListener("mouseleave", function() {
                        categoryTimeout = setTimeout(() => {
                            if (!categoryDropdown.matches(":hover")) categoryDropdown.style.display = "none";
                        }, 250);
                    });
                    categoryDropdown.addEventListener("mouseleave", function() {
                        categoryTimeout = setTimeout(() => {
                            categoryDropdown.style.display = "none";
                        }, 250);
                    });
                    categoryDropdown.addEventListener("mouseenter", function() {
                        if (categoryTimeout) clearTimeout(categoryTimeout);
                        categoryDropdown.style.display = "block";
                    });
                }

                // User Avatar Dropdown Logic (Custom JS từ code cũ của bạn)
                var userDropdownTrigger = document.getElementById("userDropdown");
                if (userDropdownTrigger) {
                    userDropdownTrigger.addEventListener("click", function(e) {
                        e.preventDefault();
                        var parentElement = this.closest('.user-avatar-nav'); // Hoặc this.parentElement nếu cấu trúc đơn giản hơn
                        var menu = this.nextElementSibling; // ul.dropdown-menu

                        if (parentElement && menu) {
                            const isShown = parentElement.classList.contains("show");
                            if (isShown) {
                                parentElement.classList.remove("show");
                                menu.classList.remove("show");
                                this.setAttribute('aria-expanded', 'false');
                            } else {
                                parentElement.classList.add("show");
                                menu.classList.add("show");
                                this.setAttribute('aria-expanded', 'true');
                            }
                        }
                    });
                }

                // Logic đóng user dropdown khi click ra ngoài (từ code cũ của bạn)
                document.addEventListener("click", function(e) {
                    var avatarNav = document.querySelector(".user-avatar-nav.show"); // Tìm dropdown đang mở
                    if (avatarNav && !avatarNav.contains(e.target)) { // Nếu click ra ngoài dropdown đang mở
                        var menu = avatarNav.querySelector('.dropdown-menu');
                        var trigger = avatarNav.querySelector('#userDropdown');

                        avatarNav.classList.remove("show");
                        if (menu) menu.classList.remove("show");
                        if (trigger) trigger.setAttribute('aria-expanded', 'false');
                    }
                });
            });
        </script>
    </div>
</nav>