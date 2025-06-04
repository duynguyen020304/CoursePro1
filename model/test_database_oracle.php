<?php
require_once __DIR__ . '/database.php'; // Điều chỉnh đường dẫn nếu cần

// THAY ĐỔI CÁC GIÁ TRỊ NÀY CHO PHÙ HỢP VỚI CẤU HÌNH CỦA BẠN
$db_host = getenv('DB_HOST_ORA') ?: 'localhost';
$db_user = getenv('DB_USER_ORA') ?: 'duy_admin'; // Schema DUY_ADMIN
$db_pass = getenv('DB_PASS_ORA') ?: 'duyadmin';
$db_service = getenv('DB_SERVICE_ORA') ?: 'QUANLYKHOAHOC';
$db_port = (int)(getenv('DB_PORT_ORA') ?: 1521);
$db_charset = getenv('DB_CHARSET_ORA') ?: 'AL32UTF8';

echo "Attempting to connect to Oracle...\n";
$db = new Database($db_host, $db_user, $db_pass, $db_service, $db_port, $db_charset);

if (!$db->isConnected()) {
    echo "Failed to connect to database. Last Error: " . htmlspecialchars($db->getLastError() ?? 'Unknown connection error.') . "\n";
    exit;
}
echo "Connected successfully.\n";

$testUserID = 'test_user_' . uniqid();
$testEmail = $testUserID . '@example.com';
$hashedPassword = password_hash('password123', PASSWORD_DEFAULT);

$sql = "INSERT INTO USERS (UserID, FirstName, LastName, Email, Password, RoleID, ProfileImage)
        VALUES (:userID, :firstName, :lastName, :email, :password, :roleID, :profileImage)";

$bindParams = [
    ':userID'       => $testUserID,
    ':firstName'    => 'Test',
    ':lastName'     => 'User',
    ':email'        => $testEmail,
    ':password'     => $hashedPassword,
    ':roleID'       => 'instructor', // Hardcoded
    ':profileImage' => null,
];

echo "Attempting to execute prepared statement with hardcoded RoleID 'instructor'...\n";
error_log("Minimal Test - SQL: " . $sql);
error_log("Minimal Test - Bind Params: " . print_r($bindParams, true));

$stid = $db->executePrepared($sql, $bindParams);

if ($stid !== false && $db->getAffectedRows() === 1) {
    echo "Minimal test INSERT successful for UserID: " . $testUserID . "\n";
} else {
    echo "Minimal test INSERT FAILED.\n";
    echo "Last DB Error: " . htmlspecialchars($db->getLastError() ?? 'No specific error message from DB class.') . "\n";
    echo "Last Query Attempted by DB class: " . htmlspecialchars($db->getLastQuery() ?? 'N/A') . "\n";
    if ($stid === false) {
        echo "executePrepared returned false.\n";
    } else {
        echo "executePrepared returned a statement handle, but affected rows was not 1. Affected rows: " . $db->getAffectedRows() . "\n";
    }
}

$db->close();
?>