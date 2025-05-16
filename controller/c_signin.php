<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Xóa lỗi đăng nhập cũ (nếu có) từ lần thử trước
unset($_SESSION['login_error']);
// Giữ lại tên đăng nhập đã nhập nếu có, hoặc xóa nếu không muốn giữ lại giữa các lần thử
// unset($_SESSION['submitted_username']); // Tùy chọn: xóa nếu không muốn điền lại username

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../signin.php');
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

// Lưu tên đăng nhập đã nhập vào session để điền lại trên form
$_SESSION['submitted_username'] = $username;

if (empty($username) || empty($password)) {
    // Nếu muốn thông báo lỗi cụ thể cho trường trống, bạn có thể đặt ở đây
    // Ví dụ: $_SESSION['login_error'] = "Vui lòng nhập đầy đủ email và mật khẩu.";
    // Tuy nhiên, theo yêu cầu là "sai tên đăng nhập hoặc mật khẩu", nên có thể bỏ qua lỗi này
    // và để API xử lý, hoặc đặt một thông báo chung.
    // Để đơn giản, nếu trường trống, API có thể sẽ báo lỗi, và ta sẽ hiển thị thông báo chung.
    // Nếu API không báo lỗi cho trường trống mà chỉ không tìm thấy user, thì thông báo chung vẫn phù hợp.
    // Nếu muốn bắt lỗi trường trống ngay tại đây:
    // $_SESSION['login_error'] = "Email và mật khẩu không được để trống.";
    // header('Location: ../signin.php');
    // exit;
}

$payload = [
    'email'    => $username,
    'password' => $password,
];

$apiUrl = 'http://localhost/CoursePro1/api/login_api.php';

$ch = curl_init($apiUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
    CURLOPT_POSTFIELDS     => json_encode($payload),
    CURLOPT_TIMEOUT        => 10,
]);

$responseJson = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

$login_failed_message = "Sai tên đăng nhập hoặc mật khẩu.";

if ($curlError) {
    // Lỗi kết nối đến API
    $_SESSION['login_error'] = "Lỗi kết nối đến máy chủ xác thực. Vui lòng thử lại sau.";
    // Hoặc có thể dùng thông báo chung: $_SESSION['login_error'] = $login_failed_message;
    header('Location: ../signin.php');
    exit;
}

$responseData = json_decode($responseJson, true);

if ($responseData === null && json_last_error() !== JSON_ERROR_NONE) {
    // Lỗi xử lý phản hồi từ API (JSON không hợp lệ)
    $_SESSION['login_error'] = "Lỗi xử lý phản hồi từ máy chủ. Vui lòng thử lại sau.";
    // Hoặc: $_SESSION['login_error'] = $login_failed_message;
    header('Location: ../signin.php');
    exit;
}

if ($httpCode === 200 && isset($responseData['success']) && $responseData['success'] === true) {
    // Đăng nhập thành công
    session_regenerate_id(true);
    $_SESSION['user'] = [
        'email'  => $responseData['email'] ?? $username,
        'roleID' => $responseData['roleID'] ?? null,
        'userID' => $responseData['userID'] ?? null,
        'firstName'   => $responseData['firstName'] ?? null,
        'lastName' => $responseData['lastName'] ?? null,
        'profileImage' => $responseData['profileImage'] ?? null,
        'token' => $responseData['token'] ?? null
    ];
    unset($_SESSION['submitted_username']); // Xóa username đã lưu khi đăng nhập thành công
    unset($_SESSION['login_error']); // Xóa lỗi nếu có
    header('Location: ../home.php');
    exit;
} else {
    // Đăng nhập thất bại (sai thông tin, hoặc lỗi khác từ API)
    // API có thể trả về một message cụ thể, ví dụ: $responseData['message']
    // Nếu có $responseData['message'], bạn có thể sử dụng nó:
    // $_SESSION['login_error'] = !empty($responseData['message']) ? htmlspecialchars($responseData['message']) : $login_failed_message;
    // Tuy nhiên, theo yêu cầu chỉ hiển thị "Sai tên đăng nhập hoặc mật khẩu":
    $_SESSION['login_error'] = $login_failed_message;
    header('Location: ../signin.php');
    exit;
}
?>
