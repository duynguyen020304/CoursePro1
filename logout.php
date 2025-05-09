<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Hủy mọi dữ liệu session user
unset($_SESSION['user']);
session_destroy();
header('Location: home.php');
exit;
