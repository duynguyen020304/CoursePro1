<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$_SESSION['debug_messages'] = [];
$_SESSION['debug_messages'][] = "c_signin.php: Script execution started.";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    $_SESSION['debug_messages'][] = "c_signin.php: Invalid request method: " . $_SERVER['REQUEST_METHOD'] . ". Exiting.";
    exit('Phương thức không được phép.');
}
$_SESSION['debug_messages'][] = "c_signin.php: Request method is POST.";

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$_SESSION['debug_messages'][] = "c_signin.php: Raw POST data - username: '{$username}', password: '(hidden)'.";

if (empty($username) || empty($password)) {
    $_SESSION['error_message'] = 'Vui lòng nhập đầy đủ email và mật khẩu.';
    $_SESSION['debug_messages'][] = "c_signin.php: Validation failed - username or password empty. Redirecting to signin.php.";
    header('Location: ../signin.php');
    exit;
}
$_SESSION['debug_messages'][] = "c_signin.php: Input validation passed.";

$payload = [
    'email'    => $username,
    'password' => $password,
];
$_SESSION['debug_messages'][] = "c_signin.php: Payload prepared: " . json_encode($payload);

$apiUrl = 'http://localhost/CoursePro1/api/login_api.php';
$_SESSION['debug_messages'][] = "c_signin.php: API URL: " . $apiUrl;

$ch = curl_init($apiUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
    CURLOPT_POSTFIELDS     => json_encode($payload),
    CURLOPT_TIMEOUT        => 10,
]);

$_SESSION['debug_messages'][] = "c_signin.php: cURL initialized. Executing request to API.";
$responseJson = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

$_SESSION['debug_messages'][] = "c_signin.php: cURL request executed. HTTP Code: " . $httpCode;
$_SESSION['debug_messages'][] = "c_signin.php: Raw API Response: " . $responseJson;
if ($curlError) {
    $_SESSION['debug_messages'][] = "c_signin.php: cURL error: " . $curlError;
}

if ($curlError) {
    $_SESSION['error_message'] = 'Lỗi kết nối đến máy chủ xác thực: ' . $curlError;
    $_SESSION['debug_messages'][] = "c_signin.php: Redirecting to signin.php due to cURL error.";
    header('Location: ../signin.php');
    exit;
}

$responseData = json_decode($responseJson, true);
$_SESSION['debug_messages'][] = "c_signin.php: API response JSON decoded. Result: " . var_export($responseData, true);

if ($responseData === null && json_last_error() !== JSON_ERROR_NONE) {
    $_SESSION['error_message'] = 'Lỗi xử lý phản hồi từ máy chủ xác thực. Mã HTTP: ' . $httpCode . '. Lỗi JSON: ' . json_last_error_msg();
    $_SESSION['debug_messages'][] = "c_signin.php: JSON Decode Error: " . json_last_error_msg() . ". Redirecting to signin.php.";
    header('Location: ../signin.php');
    exit;
}

if ($httpCode === 200 && isset($responseData['success']) && $responseData['success'] === true) {
    $_SESSION['debug_messages'][] = "c_signin.php: Login successful via API.";
    session_regenerate_id(true);
    $_SESSION['debug_messages'][] = "c_signin.php: Session regenerated.";

    $_SESSION['user'] = [
        'email'  => $responseData['email'] ?? $username,
        'roleID' => $responseData['roleID'] ?? null,
        'userID' => $responseData['userID'] ?? null,
        'name'   => $responseData['name'] ?? null,
    ];
    $_SESSION['debug_messages'][] = "c_signin.php: User data set in session: " . json_encode($_SESSION['user']);

    unset($_SESSION['error_message']);
    $_SESSION['debug_messages'][] = "c_signin.php: Redirecting to home.php.";
    header('Location: ../home.php');
    exit;
} else {
    $_SESSION['error_message'] = $responseData['message'] ?? 'Tên đăng nhập hoặc mật khẩu không đúng. (Mã HTTP: ' . $httpCode . ')';
    $_SESSION['debug_messages'][] = "c_signin.php: Login failed or API error. Message: '{$_SESSION['error_message']}'. HTTP Code: {$httpCode}. Redirecting to signin.php.";
    header('Location: ../signin.php');
    exit;
}
