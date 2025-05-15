<?php
// Đặt đúng tên folder của bạn trên webserver, ví dụ '/COURSEPRO1'
define('BASE_URL', '/CoursePro1');

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Course Online</title>
    <!-- CSS từ public/css -->
    <link href="<?= BASE_URL ?>/public/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/public/css/style.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/public/css/font_awesome_all.min.css" rel="stylesheet">
    <!-- CSS riêng cho từng page có thể include ngay sau đây -->
</head>

<body>