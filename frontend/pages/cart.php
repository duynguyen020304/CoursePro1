<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// --- START: Updated Path and API Configuration ---
// Using the more robust path detection from home.php for consistency
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http');
$host = $_SERVER['HTTP_HOST'];
$script_path = $_SERVER['SCRIPT_NAME'];

$path_parts_original = explode('/', dirname($script_path));
if (count($path_parts_original) > 1 && $path_parts_original[1] !== '') {
    $app_root_path_relative_original = implode('/', array_slice($path_parts_original, 0, count($path_parts_original)));
    if ($app_root_path_relative_original === '/' || $app_root_path_relative_original === '\\') $app_root_path_relative_original = '';
} else {
    $app_root_path_relative_original = '';
}
$app_root_path_relative = rtrim($app_root_path_relative_original, '/');

// Define base constants
define('APP_BASE_URL', $protocol . '://' . $host . $app_root_path_relative);
define('API_BASE', APP_BASE_URL . '/api');
define('CONTROLLER_FILE_PATH',../../backend/Controller/Form/c_file_loader.php');
define('APP_ROOT_PATH_RELATIVE', $app_root_path_relative);
$file_loader_base_url = APP_BASE_URL . CONTROLLER_FILE_PATH;
$defaultCourseImage = APP_BASE_URL . '/frontend/assets/img/no_image_600_400.svg';


// --- START: Updated callApi Function ---
// Using the more versatile callApi function from home.php
if (!function_exists('callApi')) {
    function callApi(string $endpointUrl, string $method = 'GET', array $payload = []): array
    {
        $url = $endpointUrl;
        $methodUpper = strtoupper($method);

        // Append query string for GET requests if not already present
        if ($methodUpper === 'GET' && !empty($payload) && strpos($url, '?') === false) {
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
                'timeout'       => 15
            ]
        ];

        // Add body for non-GET requests
        if ($methodUpper !== 'GET' && $methodUpper !== 'HEAD') {
            if (!empty($payload)) {
                $options['http']['content'] = json_encode($payload);
            } else if (in_array($methodUpper, ['POST', 'PUT'])) {
                // Send empty JSON object if payload is empty for POST/PUT
                $options['http']['content'] = '{}';
            }
        }

        $context  = stream_context_create($options);
        $response = @file_get_contents($url, false, $context);
        $responseHeaders = $http_response_header ?? [];

        $status_code = null;
        if (!empty($responseHeaders)) {
            foreach ($responseHeaders as $header) {
                if (preg_match('{HTTP/\S*\s(\d{3})}', $header, $match)) {
                    $status_code = intval($match[1]);
                    break;
                }
            }
        }

        if ($response === false) {
            return [
                'success' => false,
                'message' => 'Failed to connect to the API endpoint: ' . $url,
                'data' => null,
                'http_status_code' => $status_code ?? 0
            ];
        }

        $result = json_decode($response, true);
        $json_error = json_last_error();

        if ($result === null && $json_error !== JSON_ERROR_NONE) {
            return [
                'success' => false,
                'message' => 'Invalid API response or failed to decode JSON. Error: ' . json_last_error_msg(),
                'data' => null,
                'raw_response' => substr($response, 0, 500),
                'http_status_code' => $status_code
            ];
        }

        // Standardize the response format
        if (!is_array($result)) {
            if ($result === null && ($status_code >= 200 && $status_code < 300)) {
                return ['success' => true, 'message' => 'Operation successful with empty response.', 'data' => null, 'http_status_code' => $status_code];
            }
            return ['success' => false, 'message' => 'API response was not in the expected array format.', 'data' => $result, 'raw_response' => substr($response, 0, 500), 'http_status_code' => $status_code];
        }

        if (!isset($result['http_status_code'])) {
            $result['http_status_code'] = $status_code;
        }
        if (!isset($result['success'])) {
            $result['success'] = ($status_code >= 200 && $status_code < 300);
        }
        return $result;
    }
}
// --- END: Updated callApi Function ---


if (!function_exists('format_cart_price')) {
    function format_cart_price($price)
    {
        if (!is_numeric($price)) return 'N/A';
        return number_format($price, 0, ',', '.') . ' VNĐ';
    }
}

// --- START: Cart Data Fetching ---
$cart_items_detailed = [];
$total_cart_price = 0;
$cart_error_message = null;
$current_cart_id_page = null;
$js_user_id = null;

if (!isset($_SESSION['user']['token']) || !isset($_SESSION['user']['userID'])) {
    $cart_error_message = "Bạn cần đăng nhập để xem giỏ hàng.";
} else {
    $js_user_id = $_SESSION['user']['userID'];
    $cart_api_response = callApi(API_BASE . '/cart_api.php', 'GET');

    if (isset($cart_api_response['success']) && $cart_api_response['success']) {
        $current_cart_id_page = $cart_api_response['data']['cartID'] ?? $cart_api_response['cartID'] ?? null;
        if (!$current_cart_id_page) {
            $cart_error_message = "Không thể lấy thông tin giỏ hàng của bạn (không tìm thấy cartID).";
        }
    } else {
        $cart_error_message = "Lỗi khi lấy thông tin giỏ hàng: " . ($cart_api_response['message'] ?? 'Unknown error from cart_api');
    }

    if ($current_cart_id_page && !$cart_error_message) {
        $cart_items_api_response = callApi(API_BASE . '/cart_item_api.php', 'GET', ['cartID' => $current_cart_id_page]);

        if (isset($cart_items_api_response['status']) && $cart_items_api_response['status'] === 'success' && isset($cart_items_api_response['data'])) {
            $items_from_api = $cart_items_api_response['data'];

            $unique_cart_items = [];
            $seen_course_ids = [];
            foreach ($items_from_api as $item) {
                if (isset($item['courseID'])) {
                    if (!in_array($item['courseID'], $seen_course_ids)) {
                        $unique_cart_items[] = $item;
                        $seen_course_ids[] = $item['courseID'];
                    } else {
                        callApi(API_BASE . '/cart_item_api.php', 'DELETE', ['cartItemID' => $item['cartItemID']]);
                        error_log("Auto-removed duplicate cart item: cartItemID=" . $item['cartItemID'] . ", courseID=" . $item['courseID']);
                    }
                }
            }
            $items_to_process = $unique_cart_items;

            if (!empty($items_to_process)) {
                foreach ($items_to_process as $item) {
                    if (isset($item['courseID'])) {
                        $course_details_response = callApi(API_BASE . '/course_api.php', 'GET', ['courseID' => $item['courseID']]);
                        if (isset($course_details_response['success']) && $course_details_response['success'] && isset($course_details_response['data'])) {
                            $course_data_array = isset($course_details_response['data']['courseID']) ? [$course_details_response['data']] : $course_details_response['data'];

                            foreach ($course_data_array as $course_detail) {
                                if (isset($course_detail['courseID']) && $course_detail['courseID'] == $item['courseID']) {
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
                                    break;
                                }
                            }
                        } else {
                            error_log("Failed to fetch details for courseID: " . $item['courseID'] . " - Msg: " . ($course_details_response['message'] ?? 'Unknown error'));
                        }
                    }
                }
            }
        } else {
            $cart_error_message = "Lỗi khi lấy các mục trong giỏ hàng: " . ($cart_items_api_response['message'] ?? 'Unknown error');
        }
    }
}
// --- END: Cart Data Fetching ---


// --- START: Recommended Courses Fetching ---
$recommended_courses_data = [];
$recommend_error_message = '';
if (isset($_SESSION['user']['userID'])) {
    $userIdForApi = $_SESSION['user']['userID'];
    $recommendationApiUrl = 'http://localhost:5000/recommend/' . $userIdForApi;
    $recommendApiResponse = callApi($recommendationApiUrl, 'GET');

    if (isset($recommendApiResponse['success']) && $recommendApiResponse['success'] === true && !empty($recommendApiResponse['data'])) {
        $recommended_courses_data = $recommendApiResponse['data'];
    } else {
        $recommend_error_message = "Không thể tải các khóa học gợi ý lúc này. " . ($recommendApiResponse['message'] ?? '');
    }
}
// --- END: Recommended Courses Fetching ---


$js_vars_for_cart = [
    'apiBase' => API_BASE,
    'userToken' => $_SESSION['user']['token'] ?? null,
    'userId' => $js_user_id,
    'currentCartId' => $current_cart_id_page,
    'cartItems' => $cart_items_detailed,
    'totalCartPrice' => $total_cart_price,
    'isUserLoggedIn' => isset($_SESSION['user']['token']),
    'signInUrl' => APP_ROOT_PATH_RELATIVE . '/signin.php',
    'appRoot' => APP_ROOT_PATH_RELATIVE
];
?>

<?php include(__DIR__ . '/../templates/head.php'); ?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link href="https://unpkg.com/swiper/swiper-bundle.min.css" rel="stylesheet">
<link href="<?php echo APP_ROOT_PATH_RELATIVE; ?>/frontend/assets/css/cart.css" rel="stylesheet">
<!-- Adding some styles for course cards similar to home.php -->
<style>
    .course-card {
        background: #fff;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    .course-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }

    .course-card img {
        width: 100%;
        height: 180px;
        object-fit: cover;
    }

    .course-card .card-body {
        padding: 15px;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
    }

    .course-card .card-title {
        font-size: 1rem;
        font-weight: 600;
        margin-bottom: 10px;
        flex-grow: 1;
    }

    .course-card .card-title a {
        text-decoration: none;
        color: #333;
    }

    .course-card .course-price-cta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: auto;
        padding-top: 10px;
    }

    .course-card .course-price-cta p {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--main-color);
        margin: 0;
    }
</style>
<script type="text/javascript">
    const CART_PAGE_VARS = <?php echo json_encode($js_vars_for_cart); ?>;
</script>
<?php include(__DIR__ . '/../templates/header.php'); ?>

<section class="cart-section py-5">
    <div class="container" style="padding-top: 100px;">
        <h2 class="text-center mb-4">Các Khóa Học Trong Giỏ Hàng</h2>

        <?php if ($cart_error_message) : ?>
            <div class="alert alert-warning text-center" role="alert">
                <?php echo htmlspecialchars($cart_error_message); ?>
                <?php if (!isset($_SESSION['user']['token'])) : ?>
                    <br><a href="<?php echo APP_ROOT_PATH_RELATIVE; ?>/signin.php" class="btn btn-primary mt-2">Đăng nhập</a>
                <?php endif; ?>
            </div>
        <?php elseif (empty($cart_items_detailed)) : ?>
            <div class="alert alert-info text-center" role="alert">
                Giỏ hàng của bạn hiện đang trống.
                <br><a href="<?php echo APP_ROOT_PATH_RELATIVE; ?>/home.php" class="btn btn-outline-primary mt-2">Tiếp tục mua sắm</a>
            </div>
        <?php else : ?>
            <div class="row">
                <div class="col-lg-8">
                    <div class="cart-items">
                        <?php foreach ($cart_items_detailed as $item) : ?>
                            <div class="cart-item" id="cart-item-row-<?php echo htmlspecialchars($item['cartItemID']); ?>">
                                <div class="row">
                                    <div class="col-md-3">
                                        <?php
                                        $image_url = $defaultCourseImage;
                                        if (!empty($item['imagePath']) && !empty($item['courseID'])) {
                                            $image_filename = basename($item['imagePath']);
                                            $image_url = htmlspecialchars($file_loader_base_url . "?act=serve_image&course_id=" . urlencode($item['courseID']) . "&image=" . urlencode($image_filename));
                                        }
                                        ?>
                                        <img src="<?php echo $image_url; ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="img-fluid cart-item-image" onerror="this.onerror=null;this.src='<?php echo $defaultCourseImage; ?>';">
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
                                            } else {
                                                echo 'N/A';
                                            }
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
                <div class="col-lg-4">
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

<!-- --- START: Recommended Courses Section --- -->
<?php if (!empty($recommended_courses_data)) : ?>
    <section class="recommended-courses py-5 bg-light">
        <div class="container">
            <h2 class="text-center mb-4">Khóa Học Gợi Ý Cho Bạn</h2>

            <?php if (!empty($recommend_error_message)) : ?>
                <div class="alert alert-warning text-center"><?php echo htmlspecialchars($recommend_error_message); ?></div>
            <?php endif; ?>

            <div class="swiper recommended-courses-slider">
                <div class="swiper-wrapper">
                    <?php foreach ($recommended_courses_data as $course) : ?>
                        <?php
                        $courseImageUrl = $defaultCourseImage;
                        if (!empty($course['images']) && isset($course['images'][0]['imagePath'])) {
                            $imageFileName = $course['images'][0]['imagePath'];
                            $courseImageUrl = APP_BASE_URL . CONTROLLER_FILE_PATH . '?act=serve_image&course_id=' . urlencode($course['courseID']) . '&image=' . urlencode($imageFileName);
                        }
                        ?>
                        <div class="swiper-slide h-auto">
                            <div class="course-card">
                                <img src="<?php echo htmlspecialchars($courseImageUrl); ?>" alt="<?php echo htmlspecialchars($course['title']); ?>" onerror="this.onerror=null;this.src='<?php echo $defaultCourseImage; ?>';">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <a href="course-detail.php?courseID=<?php echo htmlspecialchars($course['courseID']); ?>"><?php echo htmlspecialchars($course['title']); ?></a>
                                    </h5>
                                    <div class="course-price-cta">
                                        <p><?php echo number_format($course['price'] ?? 0, 0, ',', '.'); ?> VNĐ</p>
                                        <a href="course-detail.php?courseID=<?php echo htmlspecialchars($course['courseID']); ?>" class="btn btn-sm btn-outline-warning">Xem Chi Tiết</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <!-- Add Pagination -->
                <div class="swiper-pagination"></div>
                <!-- Add Navigation -->
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
            </div>
        </div>
    </section>
<?php endif; ?>
<!-- --- END: Recommended Courses Section --- -->


<script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
<script>
    async function deleteCartItem(cartItemID) {
        if (!confirm('Bạn có chắc chắn muốn xóa khóa học này khỏi giỏ hàng?')) return;

        const {
            userToken,
            signInUrl,
            apiBase
        } = CART_PAGE_VARS;

        if (!userToken) {
            alert('Lỗi xác thực. Vui lòng đăng nhập lại.');
            window.location.href = signInUrl;
            return;
        }

        try {
            const response = await fetch(`${apiBase}/cart_item_api.php`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + userToken
                },
                body: JSON.stringify({
                    cartItemID: cartItemID
                })
            });

            const resultText = await response.text();
            let result;
            try {
                result = JSON.parse(resultText);
            } catch (e) {
                console.error("JSON Parse Error: ", resultText);
                alert('Lỗi xử lý phản hồi từ máy chủ.');
                return;
            }

            if (response.ok && (result.success || result.status === 'success')) {
                alert(result.message || 'Xóa khóa học thành công!');
                location.reload();
            } else {
                alert('Lỗi khi xóa: ' + (result.message || result.status || 'Unknown error'));
            }
        } catch (error) {
            console.error('Error deleting cart item:', error);
            alert('Lỗi kết nối khi xóa sản phẩm. Vui lòng thử lại.');
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const checkoutButton = document.getElementById('checkoutBtn');
        if (checkoutButton) {
            checkoutButton.addEventListener('click', handleCheckout);
        }

        // --- START: Swiper Initializer ---
        function initSwiperIfPresent(selector, options) {
            const swiperElement = document.querySelector(selector);
            if (swiperElement && swiperElement.querySelector('.swiper-wrapper') && swiperElement.querySelector('.swiper-wrapper').children.length > 0) {
                new Swiper(selector, options);
            } else if (swiperElement) {
                const navNext = swiperElement.querySelector('.swiper-button-next');
                const navPrev = swiperElement.querySelector('.swiper-button-prev');
                if (navNext) navNext.style.display = 'none';
                if (navPrev) navPrev.style.display = 'none';
            }
        }

        initSwiperIfPresent('.recommended-courses-slider', {
            slidesPerView: 4,
            spaceBetween: 30,
            loop: false,
            navigation: {
                nextEl: '.recommended-courses-slider .swiper-button-next',
                prevEl: '.recommended-courses-slider .swiper-button-prev',
            },
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
            breakpoints: {
                320: {
                    slidesPerView: 1,
                    spaceBetween: 10
                },
                576: {
                    slidesPerView: 2,
                    spaceBetween: 20
                },
                768: {
                    slidesPerView: 3,
                    spaceBetween: 30
                },
                1200: {
                    slidesPerView: 4,
                    spaceBetween: 30
                }
            }
        });
        // --- END: Swiper Initializer ---
    });

    async function handleCheckout() {
        const {
            isUserLoggedIn,
            cartItems,
            userToken,
            userId,
            currentCartId,
            totalCartPrice,
            signInUrl,
            appRoot,
            apiBase
        } = CART_PAGE_VARS;

        if (!isUserLoggedIn) {
            alert('Bạn cần đăng nhập để tiến hành thanh toán.');
            window.location.href = signInUrl;
            return;
        }
        if (!cartItems || cartItems.length === 0) {
            alert('Giỏ hàng của bạn đang trống.');
            return;
        }
        if (!userToken || !userId || !currentCartId) {
            alert('Lỗi thông tin người dùng hoặc giỏ hàng. Vui lòng thử đăng nhập lại.');
            return;
        }

        const checkoutButton = document.getElementById('checkoutBtn');
        checkoutButton.disabled = true;
        checkoutButton.textContent = 'Đang xử lý...';

        try {
            // 1. Create Order
            const orderPayload = {
                userID: userId,
                totalAmount: totalCartPrice,
                orderDate: new Date().toISOString().slice(0, 19).replace('T', ' ')
            };
            const orderResponse = await fetch(`${apiBase}/order_api.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + userToken
                },
                body: JSON.stringify(orderPayload)
            });
            const orderData = await orderResponse.json();
            if (!orderResponse.ok || !orderData.success || !orderData.data || !orderData.data.orderID) {
                throw new Error('Tạo đơn hàng thất bại: ' + (orderData.message || 'Lỗi không xác định'));
            }
            const newOrderID = orderData.data.orderID;

            // 2. Create Order Details
            for (const item of cartItems) {
                const detailPayload = {
                    orderID: newOrderID,
                    courseID: item.courseID,
                    price: item.raw_price
                };
                const detailResponse = await fetch(`${apiBase}/order_detail_api.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': 'Bearer ' + userToken
                    },
                    body: JSON.stringify(detailPayload)
                });
                const detailData = await detailResponse.json();
                if (!detailResponse.ok || !detailData.success) {
                    throw new Error(`Thêm chi tiết đơn hàng thất bại cho khóa học ${item.courseID}: ` + (detailData.message || 'Lỗi không xác định'));
                }
            }

            // 3. Create Payment
            const paymentPayload = {
                orderID: newOrderID,
                amount: totalCartPrice,
                paymentDate: new Date().toISOString().slice(0, 19).replace('T', ' '),
                paymentMethod: "Thanh toán Online",
                paymentStatus: "Completed"
            };
            const paymentResponse = await fetch(`${apiBase}/payment_api.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + userToken
                },
                body: JSON.stringify(paymentPayload)
            });
            const paymentData = await paymentResponse.json();
            if (!paymentResponse.ok || !paymentData.success) {
                throw new Error('Ghi nhận thanh toán thất bại: ' + (paymentData.message || 'Lỗi không xác định'));
            }

            // 4. Clear Cart
            const clearCartResponse = await fetch(`${apiBase}/cart_item_api.php`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + userToken
                },
                body: JSON.stringify({
                    cartID: currentCartId
                })
            });
            const clearCartData = await clearCartResponse.json();
            if (!clearCartResponse.ok || (clearCartData.status !== 'success' && !clearCartData.success)) {
                console.warn('Không thể xóa giỏ hàng sau khi thanh toán: ' + (clearCartData.message || 'Lỗi không xác định'));
            }

            alert('Thanh toán thành công! Cảm ơn bạn đã mua hàng.');
            window.location.href = appRoot + '/home.php';

        } catch (error) {
            console.error('Lỗi trong quá trình thanh toán:', error);
            alert('Đã xảy ra lỗi trong quá trình thanh toán: ' + error.message);
        } finally {
            checkoutButton.disabled = false;
            checkoutButton.textContent = 'Tiến Hành Thanh Toán';
        }
    }
</script>

<?php include(__DIR__ . '/../templates/footer.php'); ?>