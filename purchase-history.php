<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

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
    define('APP_ROOT_PATH_RELATIVE_PURCHASE', $app_root_path_relative);
} else {
    define('APP_ROOT_PATH_RELATIVE_PURCHASE', APP_ROOT_PATH_RELATIVE_HEADER);
}
if (!defined('API_BASE_HEADER')) {
    define('API_BASE_PURCHASE', $protocol . '://' . $host . APP_ROOT_PATH_RELATIVE_PURCHASE . '/api');
} else {
    define('API_BASE_PURCHASE', API_BASE_HEADER);
}

if (!function_exists('callApi')) {
    function callApi(string $endpoint, string $method = 'GET', array $payload = []): array {
        $url = API_BASE_PURCHASE . '/' . ltrim($endpoint, '/');
        $methodUpper = strtoupper($method);

        if ($methodUpper === 'GET' && !empty($payload)) {
            $url .= '?' . http_build_query($payload);
        }

        $headers_arr = ["Content-Type: application/json; charset=utf-8", "Accept: application/json"];
        $token = $_SESSION['user']['token'] ?? null;
        if ($token) {
            $headers_arr[] = "Authorization: Bearer " . $token;
        }
        $headers_str = implode("\r\n", $headers_arr);

        $options = ['http' => ['method' => $methodUpper, 'header' => $headers_str, 'ignore_errors' => true, 'timeout' => 20]];

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

        if ($response === false) return ['success' => false, 'message' => 'API connection failed. URL: ' . htmlspecialchars($url), 'http_status_code' => $status_code, 'data' => null];

        $result = json_decode($response, true);

        if ($result === null && json_last_error() !== JSON_ERROR_NONE && !empty($response)) {
            return ['success' => false, 'message' => 'Invalid API response (not JSON). Error: ' . json_last_error_msg(), 'http_status_code' => $status_code, 'data' => null, 'raw_response' => $response];
        }

        if (!is_array($result)) $result = [];
        $result['http_status_code'] = $status_code;
        if (!isset($result['success'])) $result['success'] = ($status_code >= 200 && $status_code < 300);
        return $result;
    }
}

if (!function_exists('format_purchase_price')) {
    function format_purchase_price($price) {
        if (!is_numeric($price)) return 'N/A';
        return number_format($price, 0, ',', '.') . ' VNĐ';
    }
}

$purchase_records = [];
$fetch_error_message = null;
$loggedInUserID = $_SESSION['user']['userID'] ?? null;
$user_name = $_SESSION['user']['firstName'] ?? 'Người dùng';

if (!$loggedInUserID) {
    $fetch_error_message = "Bạn cần đăng nhập để xem lịch sử mua hàng.";
} else {
    $orders_response = callApi('order_api.php', 'GET', ['userID' => $loggedInUserID]);

    if (isset($orders_response['success']) && $orders_response['success'] && !empty($orders_response['data'])) {
        $orders = $orders_response['data'];
        foreach ($orders as $order) {
            if (isset($order['orderID'])) {
                $payment_info = null;
                $payment_response = callApi('payment_api.php', 'GET', ['orderID' => $order['orderID']]);
                if (isset($payment_response['success']) && $payment_response['success'] && !empty($payment_response['data'])) {
                    $payment_info = $payment_response['data'][0] ?? $payment_response['data'];
                } else {
                    error_log("PurchaseHistory: No payment info for orderID " . $order['orderID'] . ". Msg: " . ($payment_response['message'] ?? 'Unknown'));
                }

                $purchase_date_to_use = $order['orderDate'];
                if ($payment_info && isset($payment_info['paymentDate'])) {
                    if (isset($payment_info['paymentDate']['date'])) {
                        try {
                            $dt = new DateTime($payment_info['paymentDate']['date']);
                            $purchase_date_to_use = $dt->format('Y-m-d H:i:s');
                        } catch (Exception $e) {
                            error_log("PurchaseHistory: Invalid paymentDate format for orderID " . $order['orderID'] . ". Value: " . print_r($payment_info['paymentDate'], true));
                        }
                    }
                }

                $purchase_records[] = [
                    'orderID' => htmlspecialchars($order['orderID']),
                    'purchaseDate' => $purchase_date_to_use,
                    'totalAmount' => $order['totalAmount'] ?? 0,
                    'paymentStatus' => htmlspecialchars($payment_info['paymentStatus'] ?? 'Chưa rõ'),
                    'paymentMethod' => htmlspecialchars($payment_info['paymentMethod'] ?? 'N/A'),
                    'invoiceLink' => APP_ROOT_PATH_RELATIVE_PURCHASE . '/order-receipt.php?orderID=' . urlencode($order['orderID'])
                ];
            }
        }
        if (empty($purchase_records) && !empty($orders)) {
            $fetch_error_message = "Không tìm thấy thông tin chi tiết cho các đơn hàng của bạn.";
        }
    } elseif (isset($orders_response['success']) && $orders_response['success'] && empty($orders_response['data'])) {
    } else {
        $fetch_error_message = "Không thể tải lịch sử mua hàng. " . ($orders_response['message'] ?? 'Lỗi không xác định.');
    }
}

if (!empty($purchase_records)) {
    usort($purchase_records, function($a, $b) {
        return strtotime($b['purchaseDate']) - strtotime($a['purchaseDate']);
    });
}

$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch sử mua hàng - <?php echo htmlspecialchars($user_name); ?></title>
    <link href="<?php echo APP_ROOT_PATH_RELATIVE_PURCHASE; ?>/public/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?php echo APP_ROOT_PATH_RELATIVE_PURCHASE; ?>/public/css/purchase-history.css" rel="stylesheet">
    <link href="<?php echo APP_ROOT_PATH_RELATIVE_PURCHASE; ?>/public/css/user.css" rel="stylesheet">
</head>
<body>

<?php include('template/user_sidebar.php'); ?>

<div class="main-content">
    <div class="topbar-sm d-lg-none d-flex justify-content-between align-items-center mb-3">
        <button class="btn btn-dark" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarOffcanvas" aria-controls="sidebarOffcanvas">
            <i class="bi bi-list"></i>
        </button>
        <h5 class="mb-0">Lịch sử mua hàng</h5>
        <div></div>
    </div>

    <div class="container-fluid">
        <h3 class="mb-4 pt-2 pt-lg-0 page-title-custom">Lịch sử mua hàng</h3>

        <?php if ($fetch_error_message): ?>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <?php echo htmlspecialchars($fetch_error_message); ?>
                <?php if (!$loggedInUserID): ?>
                    <a href="<?php echo APP_ROOT_PATH_RELATIVE_PURCHASE; ?>/signin.php" class="btn btn-sm btn-primary ms-3">Đăng nhập</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-body">
                <?php if (!$fetch_error_message && !empty($purchase_records)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                            <tr>
                                <th>Mã ĐH</th>
                                <th>Ngày mua</th>
                                <th class="text-end">Tổng tiền</th>
                                <th class="text-center">Trạng thái TT</th>
                                <th class="text-center">Hóa đơn</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($purchase_records as $item): ?>
                                <tr>
                                    <td>
                                        <a href="<?php echo htmlspecialchars($item['invoiceLink']); ?>" title="Xem chi tiết đơn hàng">
                                            <?php echo $item['orderID']; ?>
                                        </a>
                                    </td>
                                    <td>
                                        <?php
                                        try {
                                            $date = new DateTime($item['purchaseDate']);
                                            echo $date->format('d/m/Y H:i');
                                        } catch (Exception $e) {
                                            echo htmlspecialchars($item['purchaseDate']);
                                        }
                                        ?>
                                    </td>
                                    <td class="text-end"><?php echo format_purchase_price($item['totalAmount']); ?></td>
                                    <td class="text-center">
                                        <?php
                                        $statusClass = 'secondary';
                                        if (strtolower($item['paymentStatus']) === 'completed') $statusClass = 'success';
                                        else if (strtolower($item['paymentStatus']) === 'pending') $statusClass = 'warning';
                                        else if (strtolower($item['paymentStatus']) === 'failed') $statusClass = 'danger';
                                        ?>
                                        <span class="badge bg-<?php echo $statusClass; ?>"><?php echo $item['paymentStatus']; ?></span>
                                    </td>
                                    <td class="text-center">
                                        <a href="<?php echo htmlspecialchars($item['invoiceLink']); ?>" class="btn btn-sm btn-outline-primary" title="Xem hóa đơn">
                                            <i class="bi bi-receipt-cutoff"></i> <span class="d-none d-md-inline">Xem</span>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php elseif (!$fetch_error_message): ?>
                    <div class="alert alert-light text-center" role="alert">
                        <i class="bi bi-cart-x fs-3 d-block mb-2"></i>
                        Bạn chưa có lịch sử mua hàng nào.
                        <br>
                        <a href="<?php echo APP_ROOT_PATH_RELATIVE_PURCHASE; ?>/home.php" class="btn btn-primary mt-3">Khám phá khóa học</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <footer class="text-center text-muted mt-4 py-3 border-top">
        <small>&copy; <?php echo date('Y'); ?> Course Online. All Rights Reserved.</small>
    </footer>
</div>
<script src="<?php echo APP_ROOT_PATH_RELATIVE_PURCHASE; ?>/public/js/bootstrap.bundle.min.js"></script>
</body>
</html>