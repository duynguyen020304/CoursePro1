<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Determine the application root path relative to the current script.
// This logic helps in creating correct URLs regardless of where the header is included.
$h_protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http');
$h_host = $_SERVER['HTTP_HOST'];
$h_script_path = $_SERVER['SCRIPT_NAME'];
$h_app_root_path_relative = '';

// Known subdirectories that indicate the script is not in the root.
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

// If no known marker is found, try to determine root from script's directory.
if (!$h_found_marker) {
    $h_app_root_path_relative = dirname($h_script_path);
    // Handle cases where dirname might return '/' or '\' for scripts in the root.
    if (($h_app_root_path_relative === '/' || $h_app_root_path_relative === '\\')) {
        // If script path has no further slashes after initial one, it's likely in root.
        if (substr_count(ltrim($h_script_path, '/'), '/') == 0) {
            $h_app_root_path_relative = '';
        }
    }
}

// Clean up trailing slash if not root.
if ($h_app_root_path_relative !== '/' && $h_app_root_path_relative !== '') {
    $h_app_root_path_relative = rtrim($h_app_root_path_relative, '/');
}

// Define constants for application paths and API base URL if not already defined.
if (!defined('APP_ROOT_PATH_RELATIVE_HEADER')) {
    define('APP_ROOT_PATH_RELATIVE_HEADER', $h_app_root_path_relative);
}
if (!defined('API_BASE_HEADER')) {
    define('API_BASE_HEADER', $h_protocol . '://' . $h_host . APP_ROOT_PATH_RELATIVE_HEADER . '/api');
}

// Function to call APIs, used for header-specific data like cart count.
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
                'ignore_errors' => true, // Crucial to get body even on 4xx/5xx errors
                'timeout'       => 10 // Connection timeout in seconds
            ]
        ];

        if ($methodUpper !== 'GET' && !empty($payload)) {
            $options['http']['content'] = json_encode($payload);
        } elseif (in_array($methodUpper, ['POST', 'PUT']) && empty($payload)) {
            // Send empty JSON object for POST/PUT if payload is empty, as some APIs might expect it.
            $options['http']['content'] = '{}';
        }

        $context  = stream_context_create($options);
        $response = @file_get_contents($url, false, $context); // Use @ to suppress warnings on failure

        // Extract HTTP status code from headers
        $status_code = 0;
        if (isset($http_response_header[0]) && preg_match('{HTTP/\S*\s(\d{3})}', $http_response_header[0], $match)) {
            $status_code = intval($match[1]);
        }

        if ($response === false) {
            // Network error or similar issue preventing connection
            return ['success' => false, 'message' => 'API connection failed for header. Could not reach ' . $url, 'http_status_code' => $status_code, 'data' => null];
        }

        $result = json_decode($response, true);

        // Check for JSON decoding errors, but only if response is not empty
        if ($result === null && json_last_error() !== JSON_ERROR_NONE && !empty($response)) {
            return ['success' => false, 'message' => 'Invalid API response (not JSON) for header. Error: ' . json_last_error_msg(), 'http_status_code' => $status_code, 'data' => null, 'raw_response' => substr($response, 0, 500)];
        }

        // Ensure result is an array for consistent structure
        if (!is_array($result)) {
            $result = []; // Or handle as an error if array is strictly expected
        }

        // Standardize response structure
        $result['http_status_code'] = $status_code;
        if (!isset($result['success'])) {
            $result['success'] = ($status_code >= 200 && $status_code < 300);
        }
        if (!isset($result['data'])) {
            $result['data'] = null; // Ensure 'data' key exists
        }
        if (!isset($result['message']) && !$result['success']) {
            $result['message'] = 'API request failed with HTTP status ' . $status_code;
        } elseif (!isset($result['message']) && $result['success']) {
            $result['message'] = 'API request successful.';
        }
        return $result;
    }
}

// Calculate cart item count for the current user.
$cart_item_count = 0;
if (isset($_SESSION['user']['token']) && isset($_SESSION['user']['userID'])) {
    // Get active cart for the user
    $cart_api_response = callApiForHeader('cart_api.php', 'GET', ['userID' => $_SESSION['user']['userID'], 'isActive' => 'true']);
    $current_cart_id = null;

    if (isset($cart_api_response['success']) && $cart_api_response['success']) {
        if (isset($cart_api_response['data'])) {
            // API might return a single cart object or an array of carts
            if (isset($cart_api_response['data']['cartID'])) { // Single cart object
                $current_cart_id = $cart_api_response['data']['cartID'];
            } elseif (is_array($cart_api_response['data']) && !empty($cart_api_response['data']) && isset($cart_api_response['data'][0]['cartID'])) { // Array of carts, take the first one
                $current_cart_id = $cart_api_response['data'][0]['cartID'];
            }
        }
    }

    // If a cart is found, get its items
    if ($current_cart_id) {
        $cart_items_response = callApiForHeader('cart_item_api.php', 'GET', ['cartID' => $current_cart_id]);
        if (isset($cart_items_response['success']) && $cart_items_response['success'] && isset($cart_items_response['data']) && is_array($cart_items_response['data'])) {
            $cart_item_count = count($cart_items_response['data']);
        }
    }
}
?>

<style>
    /* Styles for the existing search input and icon button */
    .search-form .search-input-wrapper {
        position: relative; /* For positioning the suggestions dropdown */
        width: 100%; /* Ensure wrapper takes full width in its container */
        max-width: 600px; /* Optional: constrain max width like Udemy */
    }

    .search-form .search-input.form-control {
        padding-right: 3rem; /* Space for the search icon */
        border-radius: 999px; /* Rounded like Udemy */
        border: 1px solid #1c1d1f; /* Udemy-like border */
        height: 46px; /* Udemy-like height */
        padding-left: 1.5rem;
    }
    .search-form .search-input.form-control:focus {
        border-color: #1c1d1f;
        box-shadow: 0 0 0 0.2rem rgba(28,29,31,.25);
    }


    .search-form .search-icon-inside-btn {
        position: absolute;
        right: 0;
        top: 0;
        height: 100%;
        width: 3rem; /* Width of the icon button */
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: transparent;
        border: none;
        padding: 0;
        margin: 0;
        color: #1c1d1f; /* Darker icon color */
        cursor: pointer;
        z-index: 4; /* Ensure icon is clickable over input */
        border-top-right-radius: 999px; /* Match input rounding */
        border-bottom-right-radius: 999px; /* Match input rounding */
    }

    .search-form .search-icon-inside-btn:hover,
    .search-form .search-icon-inside-btn:focus {
        color: #0056b3; /* Keep hover effect or adjust as needed */
        background-color: transparent;
        border: none;
        outline: none;
        box-shadow: none;
    }

    .search-form .search-icon-inside-btn svg {
        width: 18px; /* Slightly larger icon */
        height: 18px;
    }

    /* Styles for the search suggestions dropdown */
    .search-suggestions-dropdown {
        position: absolute;
        top: 100%; /* Position below the search input */
        left: 0;
        right: 0;
        background-color: #fff;
        border: 1px solid #d1d7dc;
        border-top: none; /* Optional: if you want it to look connected */
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        z-index: 1050; /* High z-index to appear above other content */
        max-height: 400px; /* Limit height and allow scrolling */
        overflow-y: auto;
        display: none; /* Hidden by default */
        border-bottom-left-radius: 4px;
        border-bottom-right-radius: 4px;
    }

    .search-suggestions-dropdown .suggestion-item {
        display: flex; /* Use flexbox for layout */
        align-items: center; /* Vertically align items */
        padding: 10px 15px;
        cursor: pointer;
        text-decoration: none;
        color: #1c1d1f;
        border-bottom: 1px solid #f0f0f0; /* Separator line */
    }
    .search-suggestions-dropdown .suggestion-item:last-child {
        border-bottom: none;
    }


    .search-suggestions-dropdown .suggestion-item:hover {
        background-color: #f7f9fa; /* Light hover effect */
    }

    .search-suggestions-dropdown .suggestion-item-image {
        width: 60px; /* Fixed width for thumbnail */
        height: 40px; /* Fixed height for thumbnail */
        object-fit: cover; /* Ensure image covers the area */
        margin-right: 12px;
        border: 1px solid #e0e0e0; /* Light border for image */
        background-color: #f0f0f0; /* Placeholder background */
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
    .navbar-nav.right-nav { /* Ensure right nav items don't overlap with a wide search bar if navbar shrinks */
        flex-shrink: 0;
    }
    .custom-navbar .search-form { /* Allow search form to take more space */
        flex-grow: 1;
        margin-left: 1rem !important;
        margin-right: 1rem !important;
        display: flex; /* Added for centering search-input-wrapper */
        justify-content: center; /* Added for centering search-input-wrapper */
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
                // User avatar and dropdown logic
                if (isset($_SESSION['user']) && is_array($_SESSION['user']) && isset($_SESSION['user']['firstName']) && isset($_SESSION['user']['lastName']) && isset($_SESSION['user']['email'])) {
                    $user = $_SESSION['user'];
                    $avatar = '';
                    // Generate avatar initials from first and last names
                    $firstNameInitial = !empty(trim($user['firstName'])) ? mb_substr(trim($user['firstName']), 0, 1, 'UTF-8') : '';
                    $lastNameInitial  = !empty(trim($user['lastName'])) ? mb_substr(trim($user['lastName']),  0, 1, 'UTF-8') : '';

                    if ($firstNameInitial !== '' || $lastNameInitial !== '') {
                        $avatar = strtoupper($firstNameInitial . $lastNameInitial);
                    } else {
                        // Fallback to email prefix if names are empty
                        $emailPrefix = explode('@', $user['email'])[0];
                        $avatar = strtoupper(mb_substr($emailPrefix, 0, 2, 'UTF-8'));
                        if (empty($avatar)) $avatar = '??'; // Default if email prefix is also short/empty
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
                            <li><hr class="dropdown-divider my-1"></li>
                            <li><a class="dropdown-item" href="<?php echo APP_ROOT_PATH_RELATIVE_HEADER; ?>/my-courses.php"><i class="fas fa-book-open me-2"></i>Khóa học của tôi</a></li>
                            <li><a class="dropdown-item" href="<?php echo APP_ROOT_PATH_RELATIVE_HEADER; ?>/cart.php"><i class="fas fa-shopping-cart me-2"></i>Giỏ hàng
                                    <?php if ($cart_item_count > 0): ?>
                                        <span class="badge rounded-pill bg-primary ms-1"><?php echo $cart_item_count; ?></span>
                                    <?php endif; ?>
                                </a>
                            </li>
                            <li><a class="dropdown-item" href="<?php echo APP_ROOT_PATH_RELATIVE_HEADER; ?>/wishlist.php"><i class="fas fa-heart me-2"></i>Danh sách yêu thích</a></li>
                            <li><hr class="dropdown-divider my-1"></li>
                            <li><a class="dropdown-item" href="<?php echo APP_ROOT_PATH_RELATIVE_HEADER; ?>/notifications.php"><i class="fas fa-bell me-2"></i>Thông báo</a></li>
                            <li><hr class="dropdown-divider my-1"></li>
                            <li><a class="dropdown-item" href="<?php echo APP_ROOT_PATH_RELATIVE_HEADER; ?>/profile-settings.php"><i class="fas fa-cog me-2"></i>Cài đặt tài khoản</a></li>
                            <li><a class="dropdown-item" href="<?php echo APP_ROOT_PATH_RELATIVE_HEADER; ?>/purchase-history.php"><i class="fas fa-history me-2"></i>Lịch sử mua hàng</a></li>
                            <li><hr class="dropdown-divider my-1"></li>
                            <li>
                                <?php
                                // Determine profile link based on user role
                                $role_href = APP_ROOT_PATH_RELATIVE_HEADER . "/edit-profile.php"; // Default
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
                            <li><hr class="dropdown-divider my-1"></li>
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
        const appRootPath = "<?php echo APP_ROOT_PATH_RELATIVE_HEADER; ?>"; // Used for constructing URLs

        // Function to build the category tree HTML
        function buildTree(categories, parentId = null) {
            let html = '<ul class="cat-tree-menu">';
            // Filter categories that are direct children of the current parentId
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
                    html += buildTree(categories, cat.id); // Recursively build for children
                }
                html += `</li>`;
            });
            html += '</ul>';
            return html;
        }

        // Function to align the category dropdown below the button
        function alignCategoryDropdown() {
            if (!categoryBtn || !categoryDropdown) return;
            const rect = categoryBtn.getBoundingClientRect();
            categoryDropdown.style.position = "absolute";
            categoryDropdown.style.left = rect.left + window.scrollX + 'px';
            categoryDropdown.style.top = rect.bottom + window.scrollY + 'px';
            categoryDropdown.style.minWidth = Math.max(categoryBtn.offsetWidth, 250) + 'px';
            categoryDropdown.style.zIndex = 1050; // Ensure it's above other elements
        }

        let categoryTimeoutEnter = null;
        let categoryTimeoutLeave = null;

        // Event listeners for category dropdown (show/hide on hover)
        if (categoryBtn && categoryDropdown) {
            const showDropdown = () => {
                if (categoryTimeoutLeave) clearTimeout(categoryTimeoutLeave);
                alignCategoryDropdown();
                categoryDropdown.style.display = "block";

                // Load categories via API if not already loaded
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
                        categoriesLoaded = true; // Set to true even on error to prevent retrying
                    });
                }
            };

            const hideDropdown = () => {
                categoryTimeoutLeave = setTimeout(() => {
                    // Hide only if mouse is not over the dropdown itself
                    if (!categoryDropdown.matches(":hover")) {
                        categoryDropdown.style.display = "none";
                    }
                }, 200); // Small delay to allow moving mouse to dropdown
            };

            categoryBtn.addEventListener("mouseenter", () => {
                if(categoryTimeoutEnter) clearTimeout(categoryTimeoutEnter);
                categoryTimeoutEnter = setTimeout(showDropdown, 50); // Slight delay before showing
            });
            categoryBtn.addEventListener("mouseleave", hideDropdown);

            categoryDropdown.addEventListener("mouseenter", () => {
                if (categoryTimeoutLeave) clearTimeout(categoryTimeoutLeave); // Cancel hide if mouse enters dropdown
            });
            categoryDropdown.addEventListener("mouseleave", hideDropdown); // Hide if mouse leaves dropdown

            // Hide dropdown if clicked outside
            document.addEventListener("click", function(e) {
                if (categoryDropdown.style.display === 'block' && !categoryBtn.contains(e.target) && !categoryDropdown.contains(e.target)) {
                    categoryDropdown.style.display = "none";
                }
            });
            window.addEventListener('resize', alignCategoryDropdown); // Re-align on window resize
        }

        // User avatar dropdown toggle logic
        var userDropdownTrigger = document.getElementById("userDropdown");
        if (userDropdownTrigger) {
            userDropdownTrigger.addEventListener("click", function(e) {
                e.preventDefault(); // Prevent default link behavior
                var parentElement = this.closest('.user-avatar-nav');
                var menu = this.nextElementSibling; // The dropdown menu

                if (parentElement && menu) {
                    const isShown = parentElement.classList.toggle("show");
                    menu.classList.toggle("show", isShown);
                    this.setAttribute('aria-expanded', isShown.toString());
                }
            });
        }

        // Hide user dropdown if clicked outside
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

        // --- START: New Search Suggestions Logic ---
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
                    return; // Exit if search term is empty
                }

                // Suggestions will be fetched for any search term length > 0.
                // The debounce timer will manage API call frequency.

                suggestionsDropdown.style.display = 'block'; // Show dropdown immediately
                suggestionsDropdown.innerHTML = '<div class="suggestion-item-loading">Đang tìm kiếm...</div>'; // Show loading message

                debounceTimer = setTimeout(() => {
                    fetchCourseSuggestions(searchTerm);
                }, 300); // Debounce delay of 300ms
            });

            searchInput.addEventListener('focus', function() {
                const searchTerm = this.value.trim();
                // Show suggestions on focus if there's a search term and results were previously loaded
                if (searchTerm.length > 0 && suggestionsDropdown.children.length > 0 && suggestionsDropdown.innerHTML.trim() !== '' && !suggestionsDropdown.querySelector('.suggestion-item-loading')) {
                    suggestionsDropdown.style.display = 'block';
                }
            });


            async function fetchCourseSuggestions(query) {
                const searchApiUrl = `${appRootPath}/api/search_course_api.php?title=${encodeURIComponent(query)}`;
                try {
                    const response = await fetch(searchApiUrl);
                    if (!response.ok) {
                        // If API returns an error status, but valid JSON (like your API does)
                        if (response.headers.get("content-type")?.includes("application/json")) {
                            const errorData = await response.json();
                            console.error('API error when fetching suggestions:', errorData);
                            displaySuggestionsError(`Lỗi API: ${errorData.message || response.statusText}`);
                            return;
                        }
                        // For other non-ok responses that are not JSON
                        throw new Error(`Lỗi mạng hoặc máy chủ: ${response.statusText} (HTTP ${response.status})`);
                    }
                    const courses = await response.json(); // Your API returns the array directly
                    renderSuggestions(courses);
                } catch (error) {
                    console.error('Lỗi khi tìm kiếm khóa học:', error);
                    displaySuggestionsError('Không thể tải gợi ý. Vui lòng thử lại.');
                }
            }

            function renderSuggestions(courses) {
                suggestionsDropdown.innerHTML = ''; // Clear previous suggestions or loading message

                if (!courses || courses.length === 0) {
                    suggestionsDropdown.innerHTML = '<div class="suggestion-item-no-results">Không tìm thấy khóa học nào.</div>';
                    suggestionsDropdown.style.display = 'block'; // Keep it visible to show the message
                    return;
                }

                courses.forEach(course => {
                    const item = document.createElement('a');
                    item.classList.add('suggestion-item');
                    // IMPORTANT: Adjust this link to your actual course detail page structure
                    item.href = `${appRootPath}/course-detail.php?courseID=${course.courseID}`;

                    const imageContainer = document.createElement('div');
                    imageContainer.classList.add('suggestion-item-image-container'); // Though not directly styled, good for structure

                    const img = document.createElement('img');
                    img.classList.add('suggestion-item-image');
                    // Construct image URL using c_file_loader.php
                    // Assumes course.images is an array and the first element is the image filename.
                    if (course.images && course.images.length > 0 && course.images[0]) {
                        img.src = `${appRootPath}/controller/c_file_loader.php?act=serve_image&course_id=${course.courseID}&image=${encodeURIComponent(course.images[0])}`;
                        img.alt = course.title;
                    } else {
                        // Fallback placeholder if no image is available
                        img.src = `https://placehold.co/60x40/e0e0e0/777?text=N/A`;
                        img.alt = "Không có hình ảnh";
                    }
                    img.onerror = function() { // Fallback if image fails to load
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

                    // Add instructor name if available
                    if (course.instructors && course.instructors.length > 0) {
                        const instructor = course.instructors[0]; // Assuming first instructor is primary
                        const instructorSpan = document.createElement('span');
                        instructorSpan.classList.add('suggestion-item-instructor');
                        // Construct full name, handling cases where one part might be missing
                        let instructorName = `${instructor.firstName || ''} ${instructor.lastName || ''}`.trim();
                        if (instructorName) { // Only add if there's a name
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

            // Hide suggestions when clicking outside the search form or the dropdown itself
            document.addEventListener('click', function(event) {
                if (!courseSearchForm.contains(event.target) && suggestionsDropdown.style.display === 'block') {
                    suggestionsDropdown.style.display = 'none';
                }
            });

            // Optional: Hide on Escape key
            searchInput.addEventListener('keydown', function(event) {
                if (event.key === "Escape") {
                    suggestionsDropdown.style.display = 'none';
                }
            });
        }
        // --- END: New Search Suggestions Logic ---
    });
</script>
