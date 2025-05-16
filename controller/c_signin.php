<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../signin.php');
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    header('Location: ../signin.php');
    exit;
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

if ($curlError) {
    header('Location: ../signin.php');
    exit;
}

$responseData = json_decode($responseJson, true);

if ($responseData === null && json_last_error() !== JSON_ERROR_NONE) {
    header('Location: ../signin.php');
    exit;
}

if ($httpCode === 200 && isset($responseData['success']) && $responseData['success'] === true) {
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
    header('Location: ../home.php');
    exit;
} else {
    header('Location: ../signin.php');
    exit;
}