<?php
require __DIR__ . '/../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$sec_key = "DuyDepTraiVL";

$payload = array(
    'isd' => 'CoursePro1',
    'aud' => 'CoursePro1User',
    'userName' => 'duycute',
    'password' => 'duycute123'
);

$encode = JWT::encode($payload, $sec_key, 'HS256');
// echo $encode;

$header = apache_request_headers();
if ($header['Authorization']) {
    $header = $header['Authorization'];
    $decode = JWT::decode($header, new Key($sec_key, 'HS256'));
}
echo $decode->userName;
