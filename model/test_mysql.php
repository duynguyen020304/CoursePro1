<?php

// Giả sử lớp Database của bạn được lưu trong file 'Database.php'
// Hãy đảm bảo file này tồn tại trong cùng thư mục hoặc cung cấp đường dẫn chính xác.
require_once 'database.php';

echo "<h1>Kiểm tra kết nối Database</h1>";

// --- Cấu hình thông tin kết nối ---
// Thay đổi các giá trị này cho phù hợp với môi trường của bạn.
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '30112004';
$db_name = 'test_db'; 
$db_port = 3306;

// --- Bắt đầu kiểm tra ---
echo "<p>Đang cố gắng kết nối tới MySQL...</p>";
echo "<ul>";
echo "<li>Host: {$db_host}</li>";
echo "<li>Database: {$db_name}</li>";
echo "<li>User: {$db_user}</li>";
echo "</ul>";

// Khởi tạo đối tượng Database
// Lớp của bạn sẽ tự động cố gắng tạo database nếu nó không tồn tại.
$db = new Database($db_host, $db_user, $db_pass, $db_name, $db_port);

// Kiểm tra kết nối
if ($db->isConnected()) {
    echo "<p style='color: green; font-weight: bold;'>✅ Kết nối thành công!</p>";

    // Thử chạy một truy vấn đơn giản để xác nhận
    echo "<p>Thử truy vấn để lấy thời gian hiện tại từ server...</p>";
    $result = $db->fetchRow("SELECT NOW() as current_time;");

    if ($result) {
        echo "<p style='color: green; font-weight: bold;'>✅ Truy vấn thành công!</p>";
        echo "<p>Thời gian của Database Server là: <strong>" . htmlspecialchars($result['current_time']) . "</strong></p>";
    } else {
        echo "<p style='color: red; font-weight: bold;'>❌ Truy vấn thất bại.</p>";
        echo "<p>Lỗi cuối cùng: " . htmlspecialchars($db->getLastError()) . "</p>";
        echo "<p>Query cuối cùng: <pre>" . htmlspecialchars($db->getLastQuery()) . "</pre></p>";
    }
} else {
    echo "<p style='color: red; font-weight: bold;'>❌ Kết nối thất bại.</p>";
    $error = $db->getLastError();
    if ($error) {
        echo "<p>Lỗi: " . htmlspecialchars($error) . "</p>";
    } else {
        echo "<p>Không thể thiết lập kết nối. Vui lòng kiểm tra lại thông tin cấu hình (host, user, pass) và đảm bảo dịch vụ MySQL đang chạy.</p>";
    }
}

// Lớp Database của bạn có __destruct() sẽ tự động đóng kết nối,
// nhưng bạn cũng có thể gọi nó một cách tường minh nếu muốn.
$db->close();
echo "<p>Đã đóng kết nối.</p>";

?>
