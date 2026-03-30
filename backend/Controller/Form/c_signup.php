<?php
// QUAN TRỌNG: Khởi tạo session để truy cập các biến session.
// Phải được gọi trước bất kỳ output HTML nào.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Kiểm tra nếu request method không phải là POST, chuyển hướng về trang đăng ký
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../signup.php');
    exit;
}

// Lấy dữ liệu từ form và loại bỏ khoảng trắng thừa
$email            = trim($_POST['username'] ?? '');
$password         = trim($_POST['password'] ?? '');
$confirm_password = trim($_POST['confirm_password'] ?? ''); // Lấy giá trị xác nhận mật khẩu
$firstName        = trim($_POST['firstname'] ?? '');
$lastName         = trim($_POST['lastname'] ?? '');

// Lưu các giá trị đã nhập vào session để điền lại form nếu có lỗi
$_SESSION["email"] = $email;
$_SESSION["firstname"] = $firstName; // Lưu first name vào session
$_SESSION["lastname"] = $lastName;   // Lưu last name vào session

$errors = []; // Mảng chứa các thông báo lỗi

// --- VALIDATION RULES ---

// Validate Email
if (empty($email)) {
    $errors[] = 'Email không được để trống.';
    $_SESSION['error1'] = 'Email không được để trống.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Email không hợp lệ.';
    $_SESSION['error1'] = 'Email không hợp lệ.';
}

// Validate Password
if (empty($password)) {
    $errors[] = 'Mật khẩu không được để trống.';
    $_SESSION['error2'] = 'Mật khẩu không được để trống.';
} elseif (strlen($password) < 6) {
    $errors[] = 'Mật khẩu phải ít nhất 6 ký tự.';
    $_SESSION['error2'] = 'Mật khẩu phải ít nhất 6 ký tự.';
}

// Validate Confirm Password
if (empty($confirm_password)) {
    $errors[] = 'Vui lòng xác nhận mật khẩu.';
    $_SESSION['error_confirm_password'] = 'Vui lòng xác nhận mật khẩu.'; // Session error cho trường confirm password
} elseif ($password !== $confirm_password) {
    $errors[] = 'Mật khẩu xác nhận không khớp.';
    $_SESSION['error_confirm_password'] = 'Mật khẩu xác nhận không khớp.'; // Session error cho trường confirm password
}

// Validate First Name and Last Name
if ($firstName === '' || $lastName === '') {
    $errors[] = 'Họ và tên không được để trống.';
    $_SESSION['error3'] = 'Họ và tên không được để trống.';
}

// Nếu có lỗi, lưu vào session và chuyển hướng lại trang đăng ký
if (!empty($errors)) {
    $_SESSION['signup_errors'] = $errors; // Lưu tất cả lỗi vào một session array chung
    header('Location: ../signup.php');
    exit;
}

// --- API CALL ---
// Nếu không có lỗi validation, tiến hành gọi API

$payload = [
    'email'     => $email,
    'password'  => $password,
    'firstname' => $firstName,
    'lastname'  => $lastName,
    'role'      => 'student', // Hoặc vai trò khác nếu có
    'isSignup'  => true,
];

$apiUrl = 'http://localhost/CoursePro1/api/login_api.php'; // Đảm bảo URL API chính xác

$ch = curl_init($apiUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
    CURLOPT_POSTFIELDS     => json_encode($payload),
    CURLOPT_TIMEOUT        => 10, // Thời gian timeout cho request
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_errno($ch) ? curl_error($ch) : null;
curl_close($ch);

// Xử lý lỗi cURL
if ($curlError) {
    $errors[] = 'Lỗi kết nối API: ' . $curlError;
    $_SESSION['error4'] = 'Lỗi kết nối API: ' . $curlError; // Lỗi cụ thể cho API
    $_SESSION['signup_errors'] = $errors;
    header('Location: ../signup.php');
    exit;
}

// Xử lý phản hồi từ API
$result = json_decode($response, true);

// Kiểm tra lỗi giải mã JSON
if ($result === null && json_last_error() !== JSON_ERROR_NONE) {
    $errors[] = 'Lỗi xử lý phản hồi từ server (JSON decode failed).';
    $_SESSION['signup_errors'] = $errors;
    header('Location: ../signup.php');
    exit;
}

// Kiểm tra đăng ký thành công từ API
// Giả sử API trả về HTTP code 201 (Created) hoặc 200 với success:true khi thành công
if (($httpCode === 201 || ($httpCode === 200 && isset($result['success']) && $result['success'] === true)) && empty($errors)) {
    // Đăng ký thành công
    unset($_SESSION['signup_errors']); // Xóa các lỗi cũ (nếu có)
    // Xóa các giá trị đã nhập khỏi session vì đã đăng ký thành công
    unset($_SESSION['email']);
    unset($_SESSION['firstname']);
    unset($_SESSION['lastname']);
    // Xóa các session lỗi cụ thể của từng trường
    unset($_SESSION['error1'], $_SESSION['error2'], $_SESSION['error3'], $_SESSION['error4'], $_SESSION['error_confirm_password']);

    header('Location: ../signup.php?success=1'); // Chuyển hướng với tham số success
    exit;
}

// Xử lý lỗi từ API (ví dụ: email đã tồn tại, hoặc lỗi server khác)
$errorMsg = 'Đăng ký không thành công. Vui lòng thử lại.'; // Mặc định
if (isset($result['message']) && is_string($result['message'])) {
    $errorMsg = $result['message'];
} elseif (isset($result['error']) && is_string($result['error'])) { // Một số API có thể trả về 'error' thay vì 'message'
    $errorMsg = $result['error'];
}

// Nếu API trả về lỗi cụ thể cho trường nào đó (ví dụ email đã tồn tại)
// Bạn có thể muốn gán nó vào session error cụ thể, ví dụ:
// if (strpos(strtolower($errorMsg), "email") !== false && strpos(strtolower($errorMsg), "exist") !== false) {
//     $_SESSION['error1'] = $errorMsg;
// }


$_SESSION['signup_errors'] = array_merge($errors, [$errorMsg]); // Thêm lỗi từ API vào mảng lỗi chung
header('Location: ../signup.php');
exit;
?>
