<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// --- Logic để xác định $app_root_path_relative và API_BASE ---
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http');
$host = $_SERVER['HTTP_HOST'];
$script_path = $_SERVER['SCRIPT_NAME'];
$path_parts = explode('/', ltrim($script_path, '/'));
$app_root_path_relative = '';

if (count($path_parts) > 0 && $path_parts[0] !== basename($script_path)) {
    $app_root_path_relative = '/' . $path_parts[0];
}
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
    if (($app_root_path_relative === '/' || $app_root_path_relative === '\\') && $script_path !== '/') {
        $app_root_path_relative = '';
    } elseif ($app_root_path_relative === '.' && ltrim($script_path, '/') !== basename($script_path) ) {
        $app_root_path_relative = '';
    } elseif ($app_root_path_relative === '.') {
        $app_root_path_relative = '';
    }
}
if ($app_root_path_relative !== '/' && $app_root_path_relative !== '' && substr($app_root_path_relative, -1) === '/') {
    $app_root_path_relative = rtrim($app_root_path_relative, '/');
}
if ($app_root_path_relative === '/' && $script_path === '/') {
    $app_root_path_relative = '';
}

if (!defined('APP_ROOT_PATH_RELATIVE_HEADER')) {
    define('APP_ROOT_PATH_RELATIVE', $app_root_path_relative);
} else {
    define('APP_ROOT_PATH_RELATIVE', APP_ROOT_PATH_RELATIVE_HEADER);
}
if (!defined('API_BASE_HEADER')) {
    define('API_BASE', $protocol . '://' . $host . APP_ROOT_PATH_RELATIVE . '/api');
} else {
    define('API_BASE', API_BASE_HEADER);
}
$file_loader_base_url = APP_ROOT_PATH_RELATIVE . '/controller/c_file_loader.php';
// --- Kết thúc logic xác định đường dẫn ---


// --- Hàm callApi ---
if (!function_exists('callApi')) {
    function callApi(string $endpoint, string $method = 'GET', array $payload = []): array
    {
        $url = API_BASE . '/' . ltrim($endpoint, '/');
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
                'timeout'       => 15
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

        $status_code = 0;
        if (isset($http_response_header[0]) && preg_match('{HTTP\/\S*\s(\d{3})}', $http_response_header[0], $match)) {
            $status_code = intval($match[1]);
        }

        if ($response === false) {
            return ['success' => false, 'message' => 'API connection failed. URL: ' . htmlspecialchars($url), 'http_status_code' => $status_code, 'data' => null];
        }

        $result = json_decode($response, true);

        if ($result === null && json_last_error() !== JSON_ERROR_NONE && !empty($response)) {
            return ['success' => false, 'message' => 'Invalid API response (not JSON). Error: ' . json_last_error_msg(), 'http_status_code' => $status_code, 'data' => null, 'raw_response' => $response];
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
// --- Kết thúc hàm callApi ---

// --- Hàm định dạng giá ---
if (!function_exists('format_cart_price')) {
    function format_cart_price($price) {
        if (!is_numeric($price)) return 'N/A';
        return number_format($price, 0, ',', '.') . ' VNĐ';
    }
}
// --- Kết thúc hàm định dạng giá ---

// --- Lấy dữ liệu giỏ hàng ---
$cart_items_detailed = [];
$total_cart_price = 0;
$cart_error_message = null;
$current_cart_id_page = null; // Sẽ được sử dụng bởi JS để clear cart
$js_user_id = null; // Sẽ được sử dụng bởi JS để tạo order

if (!isset($_SESSION['user']['token']) || !isset($_SESSION['user']['userID'])) {
    $cart_error_message = "Bạn cần đăng nhập để xem giỏ hàng.";
} else {
    $js_user_id = $_SESSION['user']['userID']; // Lấy userID cho JS
    // 1. Lấy cartID
    $cart_api_response = callApi('cart_api.php', 'GET');
    if (isset($cart_api_response['success']) && $cart_api_response['success']) {
        if (isset($cart_api_response['cartID'])) {
            $current_cart_id_page = $cart_api_response['cartID'];
        } elseif (isset($cart_api_response['data']['cartID'])) {
            $current_cart_id_page = $cart_api_response['data']['cartID'];
        } else {
            // Xử lý lỗi typo "sucesss" nếu có
            if (isset($cart_api_response['sucesss']) && $cart_api_response['sucesss'] && isset($cart_api_response['cartID'])) {
                $current_cart_id_page = $cart_api_response['cartID'];
            } else {
                $cart_error_message = "Không thể lấy thông tin giỏ hàng của bạn (không tìm thấy cartID).";
            }
        }
    } else {
        $cart_error_message = "Lỗi khi lấy thông tin giỏ hàng: " . ($cart_api_response['message'] ?? 'Unknown error from cart_api');
    }

    // 2. Nếu có cartID, lấy cart items
    if ($current_cart_id_page && !$cart_error_message) {
        $cart_items_api_response = callApi('cart_item_api.php', 'GET', ['cartID' => $current_cart_id_page]);

        if (isset($cart_items_api_response['status']) && $cart_items_api_response['status'] === 'success' && isset($cart_items_api_response['data'])) {
            $items_from_api = $cart_items_api_response['data'];
            if (!empty($items_from_api)) {
                foreach ($items_from_api as $item) {
                    if (isset($item['courseID'])) {
                        $course_details_response = callApi('course_api.php', 'GET', ['courseID' => $item['courseID']]);
                        if (isset($course_details_response['success']) && $course_details_response['success'] && isset($course_details_response['data'])) {
                            $course_detail = $course_details_response['data'];
                            if (isset($course_detail['courseID'])) {
                                $cart_items_detailed[] = [
                                    'cartItemID' => $item['cartItemID'],
                                    'courseID' => $course_detail['courseID'],
                                    'title' => $course_detail['title'] ?? 'N/A',
                                    'price' => $course_detail['price'] ?? 0,
                                    'raw_price' => $course_detail['price'] ?? 0,
                                    'instructors' => $course_detail['instructors'] ?? [],
                                    'imagePath' => $course_detail['images'][0]['imagePath'] ?? null
                                ];
                                $total_cart_price += floatval($course_detail['price'] ?? 0);
                            } else if (is_array($course_detail) && !empty($course_detail) && isset($course_detail[0]['courseID'])) {
                                $found_match = false;
                                foreach($course_detail as $cd_item) {
                                    if($cd_item['courseID'] == $item['courseID']) {
                                        $cart_items_detailed[] = [
                                            'cartItemID' => $item['cartItemID'],
                                            'courseID' => $cd_item['courseID'],
                                            'title' => $cd_item['title'] ?? 'N/A',
                                            'price' => $cd_item['price'] ?? 0,
                                            'raw_price' => $cd_item['price'] ?? 0,
                                            'instructors' => $cd_item['instructors'] ?? [],
                                            'imagePath' => $cd_item['images'][0]['imagePath'] ?? null
                                        ];
                                        $total_cart_price += floatval($cd_item['price'] ?? 0);
                                        $found_match = true;
                                        break;
                                    }
                                }
                                if(!$found_match) error_log("Course details not found for courseID: " . $item['courseID']);
                            } else { error_log("Invalid course details structure for courseID: " . $item['courseID']); }
                        } else { error_log("Failed to fetch details for courseID: " . $item['courseID'] . " - Msg: " . ($course_details_response['message'] ?? 'Unknown error'));}
                    }
                }
            }
        } else { $cart_error_message = "Lỗi khi lấy các mục trong giỏ hàng: " . ($cart_items_api_response['message'] ?? ($cart_items_api_response['status'] ?? 'Unknown error')); }
    }
}
// --- Kết thúc lấy dữ liệu giỏ hàng ---

// Truyền biến PHP sang JavaScript
$js_vars_for_cart = [
    'apiBase' => API_BASE,
    'userToken' => $_SESSION['user']['token'] ?? null,
    'userId' => $js_user_id, // Đã lấy ở trên
    'currentCartId' => $current_cart_id_page,
    'cartItems' => $cart_items_detailed, // Mảng chi tiết các item trong giỏ hàng
    'totalCartPrice' => $total_cart_price,
    'isUserLoggedIn' => isset($_SESSION['user']['token']),
    'signInUrl' => APP_ROOT_PATH_RELATIVE . '/signin.php',
    'appRoot' => APP_ROOT_PATH_RELATIVE
];

?>

<?php include('template/head.php'); ?>
<script type="text/javascript">
    const CART_PAGE_VARS = <?php echo json_encode($js_vars_for_cart); ?>;
</script>
<link href="<?php echo APP_ROOT_PATH_RELATIVE; ?>/public/css/cart.css" rel="stylesheet">
<?php include('template/header.php'); ?>

<section class="cart-section py-5">
    <div class="container" style="padding-top: 100px;">
        <h2 class="text-center mb-4">Các Khóa Học Trong Giỏ Hàng</h2>

        <?php if ($cart_error_message): ?>
            <div class="alert alert-warning text-center" role="alert">
                <?php echo htmlspecialchars($cart_error_message); ?>
                <?php if (!isset($_SESSION['user']['token'])): ?>
                    <br><a href="<?php echo APP_ROOT_PATH_RELATIVE; ?>/signin.php" class="btn btn-primary mt-2">Đăng nhập</a>
                <?php endif; ?>
            </div>
        <?php elseif (empty($cart_items_detailed)): ?>
            <div class="alert alert-info text-center" role="alert">
                Giỏ hàng của bạn hiện đang trống.
                <br><a href="<?php echo APP_ROOT_PATH_RELATIVE; ?>/home.php" class="btn btn-outline-primary mt-2">Tiếp tục mua sắm</a>
            </div>
        <?php else: ?>
            <div class="row">
                <div class="col-md-8">
                    <div class="cart-items">
                        <?php foreach ($cart_items_detailed as $item): ?>
                            <div class="cart-item" id="cart-item-row-<?php echo htmlspecialchars($item['cartItemID']); ?>">
                                <div class="row">
                                    <div class="col-md-3">
                                        <?php
                                        $image_url = APP_ROOT_PATH_RELATIVE . "/public/images/course_placeholder.png";
                                        if (!empty($item['imagePath']) && !empty($item['courseID'])) {
                                            $image_filename = basename($item['imagePath']);
                                            $image_url = htmlspecialchars($file_loader_base_url . "?act=serve_image&course_id=" . urlencode($item['courseID']) . "&image=" . urlencode($image_filename));
                                        }
                                        ?>
                                        <img src="<?php echo $image_url; ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="img-fluid cart-item-image" onerror="this.onerror=null;this.src='<?php echo APP_ROOT_PATH_RELATIVE; ?>/public/images/course_placeholder.png';">
                                    </div>
                                    <div class="col-md-6">
                                        <h5 class="cart-item-title">
                                            <a href="<?php echo APP_ROOT_PATH_RELATIVE; ?>/course-detail.php?courseID=<?php echo htmlspecialchars($item['courseID']); ?>">
                                                <?php echo htmlspecialchars($item['title']); ?>
                                            </a>
                                        </h5>
                                        <p class="cart-item-instructor">
                                            Giảng viên:
                                            <?php
                                            if (!empty($item['instructors']) && is_array($item['instructors'])) {
                                                $instructor_names = array_map(function ($instructor) {
                                                    return htmlspecialchars(($instructor['firstName'] ?? '') . ' ' . ($instructor['lastName'] ?? ''));
                                                }, $item['instructors']);
                                                echo implode(', ', array_filter($instructor_names));
                                            } else { echo 'N/A'; }
                                            ?>
                                        </p>
                                    </div>
                                    <div class="col-md-3 cart-item-actions">
                                        <p class="cart-item-price"><?php echo format_cart_price($item['price']); ?></p>
                                        <button class="btn btn-danger btn-sm mt-2" onclick="deleteCartItem('<?php echo htmlspecialchars($item['cartItemID']); ?>')">Xóa</button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="cart-summary">
                        <h4>Tổng Cộng</h4>
                        <ul class="list-unstyled">
                            <li><strong>Tổng Tiền:</strong> <span id="total-price"><?php echo format_cart_price($total_cart_price); ?></span></li>
                        </ul>
                        <button id="checkoutBtn" class="btn btn-success btn-lg btn-block w-100">Tiến Hành Thanh Toán</button>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="recommended-courses py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-4">Khóa Học Gợi Ý Cho Bạn</h2>
        <div class="row">
            <div class="col-md-4 mb-3"> <div class="card h-100"> <img src="public/img/python.png" class="card-img-top" alt="Python" style="height: 200px; object-fit: cover;" onerror="this.onerror=null;this.src='https://placehold.co/300x200/EFEFEF/AAAAAA?text=Python';"> <div class="card-body d-flex flex-column"> <h5 class="card-title">Lập Trình Python</h5> <p class="card-text">Khóa học Python cho người mới.</p> <a href="#" class="btn btn-primary mt-auto">Xem Chi Tiết</a> </div> </div> </div>
            <div class="col-md-4 mb-3"> <div class="card h-100"> <img src="public/img/images.webp" class="card-img-top" alt="Web Design" style="height: 200px; object-fit: cover;" onerror="this.onerror=null;this.src='https://placehold.co/300x200/EFEFEF/AAAAAA?text=Web+Design';"> <div class="card-body d-flex flex-column"> <h5 class="card-title">Thiết Kế Web Hiện Đại</h5> <p class="card-text">Học HTML5, CSS3 và JavaScript.</p> <a href="#" class="btn btn-primary mt-auto">Xem Chi Tiết</a> </div> </div> </div>
            <div class="col-md-4 mb-3"> <div class="card h-100"> <img src="public/img/digital_marketing.png" class="card-img-top" alt="Marketing" style="height: 200px; object-fit: cover;" onerror="this.onerror=null;this.src='https://placehold.co/300x200/EFEFEF/AAAAAA?text=Marketing';"> <div class="card-body d-flex flex-column"> <h5 class="card-title">Digital Marketing</h5> <p class="card-text">Marketing trực tuyến hiệu quả.</p> <a href="#" class="btn btn-primary mt-auto">Xem Chi Tiết</a> </div> </div> </div>
        </div>
    </div>
</section>

<script>
    async function deleteCartItem(cartItemID) {
        if (!confirm('Bạn có chắc chắn muốn xóa khóa học này khỏi giỏ hàng?')) return;
        if (!CART_PAGE_VARS.userToken) {
            alert('Lỗi xác thực. Vui lòng đăng nhập lại.');
            window.location.href = CART_PAGE_VARS.signInUrl;
            return;
        }
        try {
            const response = await fetch(`${CART_PAGE_VARS.apiBase}/cart_item_api.php`, {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + CART_PAGE_VARS.userToken },
                body: JSON.stringify({ cartItemID: cartItemID })
            });
            const resultText = await response.text();
            let result;
            try { result = JSON.parse(resultText); }
            catch (e) {
                console.error("JSON Parse Error: ", resultText);
                alert('Lỗi xử lý phản hồi: ' + resultText.substring(0,100)); return;
            }
            if (response.ok && result.status === 'success') {
                alert(result.message || 'Xóa khóa học thành công!');
                location.reload();
            } else { alert('Lỗi khi xóa: ' + (result.message || result.status || 'Unknown error')); }
        } catch (error) { console.error('Error deleting cart item:', error); alert('Lỗi kết nối khi xóa sản phẩm.'); }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const checkoutButton = document.getElementById('checkoutBtn');
        if (checkoutButton) {
            checkoutButton.addEventListener('click', handleCheckout);
        }
    });

    async function handleCheckout() {
        if (!CART_PAGE_VARS.isUserLoggedIn) {
            alert('Bạn cần đăng nhập để tiến hành thanh toán.');
            window.location.href = CART_PAGE_VARS.signInUrl;
            return;
        }
        if (!CART_PAGE_VARS.cartItems || CART_PAGE_VARS.cartItems.length === 0) {
            alert('Giỏ hàng của bạn đang trống.');
            return;
        }
        if (!CART_PAGE_VARS.userToken || !CART_PAGE_VARS.userId || !CART_PAGE_VARS.currentCartId) {
            alert('Lỗi thông tin người dùng hoặc giỏ hàng. Vui lòng thử đăng nhập lại.');
            return;
        }

        const checkoutButton = document.getElementById('checkoutBtn');
        checkoutButton.disabled = true;
        checkoutButton.textContent = 'Đang xử lý...';

        try {
            // Bước 1: Tạo Order
            const orderPayload = {
                userID: CART_PAGE_VARS.userId,
                totalAmount: CART_PAGE_VARS.totalCartPrice,
                orderDate: new Date().toISOString() // Gửi ngày giờ chuẩn ISO
            };
            const orderResponse = await fetch(`${CART_PAGE_VARS.apiBase}/order_api.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + CART_PAGE_VARS.userToken },
                body: JSON.stringify(orderPayload)
            });
            const orderData = await orderResponse.json();
            if (!orderResponse.ok || !orderData.success || !orderData.data || !orderData.data.orderID) {
                throw new Error('Tạo đơn hàng thất bại: ' + (orderData.message || 'Lỗi không xác định từ order_api'));
            }
            const newOrderID = orderData.data.orderID;
            console.log('Order created:', newOrderID);

            // Bước 2: Tạo Order Details
            for (const item of CART_PAGE_VARS.cartItems) {
                const detailPayload = {
                    orderID: newOrderID,
                    courseID: item.courseID,
                    price: item.raw_price // Sử dụng giá gốc
                };
                const detailResponse = await fetch(`${CART_PAGE_VARS.apiBase}/order_detail_api.php`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + CART_PAGE_VARS.userToken },
                    body: JSON.stringify(detailPayload)
                });
                const detailData = await detailResponse.json();
                if (!detailResponse.ok || !detailData.success) {
                    throw new Error(`Thêm chi tiết đơn hàng thất bại cho khóa học ${item.courseID}: ` + (detailData.message || 'Lỗi không xác định'));
                }
                console.log('Order detail added for course:', item.courseID);
            }

            // Bước 3: Tạo Payment
            const paymentPayload = {
                orderID: newOrderID,
                amount: CART_PAGE_VARS.totalCartPrice,
                paymentDate: new Date().toISOString(),
                paymentMethod: "Thanh toán Online", // Giả định
                paymentStatus: "Completed" // Giả định thành công
            };
            const paymentResponse = await fetch(`${CART_PAGE_VARS.apiBase}/payment_api.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + CART_PAGE_VARS.userToken },
                body: JSON.stringify(paymentPayload)
            });
            const paymentData = await paymentResponse.json();
            if (!paymentResponse.ok || !paymentData.success) {
                throw new Error('Ghi nhận thanh toán thất bại: ' + (paymentData.message || 'Lỗi không xác định từ payment_api'));
            }
            console.log('Payment recorded:', paymentData.data);

            // Bước 4: Clear Cart
            const clearCartResponse = await fetch(`${CART_PAGE_VARS.apiBase}/cart_item_api.php`, {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + CART_PAGE_VARS.userToken },
                body: JSON.stringify({ cartID: CART_PAGE_VARS.currentCartId })
            });
            const clearCartData = await clearCartResponse.json();
            if (!clearCartResponse.ok || clearCartData.status !== 'success') {
                // Không ném lỗi ở đây vì thanh toán đã thành công, chỉ log lại
                console.warn('Không thể xóa giỏ hàng sau khi thanh toán: ' + (clearCartData.message || 'Lỗi không xác định'));
            } else {
                console.log('Cart cleared successfully.');
            }

            alert('Thanh toán thành công! Cảm ơn bạn đã mua hàng.');
            window.location.href = CART_PAGE_VARS.appRoot + '/home.php'; // Chuyển về trang chủ hoặc trang cảm ơn

        } catch (error) {
            console.error('Lỗi trong quá trình thanh toán:', error);
            alert('Đã xảy ra lỗi trong quá trình thanh toán: ' + error.message);
        } finally {
            checkoutButton.disabled = false;
            checkoutButton.textContent = 'Tiến Hành Thanh Toán';
        }
    }

</script>

<?php include('template/footer.php'); ?>
