<?php
session_start();

// Kiểm tra phương thức POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username  = $_POST['username'] ?? '';
    $password  = $_POST['password'] ?? '';
    $firstname = $_POST['firstname'] ?? '';
    $lastname  = $_POST['lastname'] ?? '';

    $errors = [];

    // Kiểm tra username từ 6–20 ký tự
    if (strlen($username) < 6 || strlen($username) > 20) {
        $errors[] = "Tên đăng nhập phải từ 6 đến 20 ký tự.";
    }

    // Kiểm tra password: tối thiểu 8 ký tự, 1 in hoa, 1 đặc biệt
    if (
        strlen($password) < 8 ||
        !preg_match('/[A-Z]/', $password) ||
        !preg_match('/[^a-zA-Z0-9]/', $password)
    ) {
        $errors[] = "Mật khẩu phải ít nhất 8 ký tự, chứa 1 chữ in hoa và 1 ký tự đặc biệt.";
    }

    if (!empty($errors)) {
        $_SESSION['signup_errors'] = $errors;
        header("Location: ../signup.php?error=1");
        exit();
    }

    // Gửi POST đến API
    $postData = json_encode([
        'username'  => $username,
        'password'  => $password,
        'firstname' => $firstname,
        'lastname'  => $lastname
    ]);

    $ch = curl_init('http://localhost/CoursePro1/api/login_api.php');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS     => $postData
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $result = json_decode($response, true);

    if ($httpCode === 201 && $result['success']) {
        header("Location: ../signup.php?success=1");
    } else {
        $_SESSION['signup_errors'] = [$result['message'] ?? 'Đăng ký thất bại'];
        header("Location: ../signup.php?error=1");
    }
    exit();
}
