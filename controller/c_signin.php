<?php
session_start(); // Chỉ gọi một lần ở đầu file
require_once __DIR__ . '/../model/bll/user_bll.php';
require_once __DIR__ . '/../model/dto/user_dto.php';
require_once __DIR__ . '/../service/service_user.php';

// Xóa dòng session_start(); thứ hai ở đây

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

// Lấy dữ liệu từ form POST
$username = $_POST['username'] ?? null;
$password = $_POST['password'] ?? null;

// Kiểm tra dữ liệu đầu vào
if (!$username || !$password) {
    $_SESSION['error_message'] = 'Vui lòng nhập tên đăng nhập và mật khẩu.'; // Thông báo lỗi rõ ràng hơn
    header('Location: ../signin.php');
    exit;
}

// Khởi tạo service và xác thực người dùng
$service = new UserService(); // Giả sử class UserService đã được định nghĩa và hoạt động đúng
$response = $service->authenticate($username, $password); // Giả sử hàm này trả về một object có thuộc tính success, data, message

if ($response && $response->success) { // Kiểm tra $response có tồn tại trước khi truy cập thuộc tính
    // Tạo mới session ID để tăng cường bảo mật (chống session fixation)
    session_regenerate_id(true);

    $_SESSION['user'] = [
        'userID' => $response->data->userID,
        'email'  => $response->data->email,
        'roleID' => $response->data->roleID,
        'name'   => $response->data->name,
    ];
    // Xóa thông báo lỗi nếu có từ lần thử trước
    unset($_SESSION['error_message']);
    header('Location: ../home.php'); // Chuyển hướng đến trang chủ hoặc dashboard
    exit;
} else {
    // Sử dụng message từ $response nếu có, nếu không thì dùng thông báo chung
    $_SESSION['error_message'] = ($response && isset($response->message)) ? $response->message : 'Tên đăng nhập hoặc mật khẩu không đúng.';
    header('Location: ../signin.php'); // Không cần truyền lỗi qua GET nữa
    exit;
}
// Xóa dấu } thừa ở cuối file gốc nếu có
?>