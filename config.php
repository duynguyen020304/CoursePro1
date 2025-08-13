<?php
// coursepro1/config.php
// Tính BASE_URI dựa trên vị trí thư mục project so với DOCUMENT_ROOT
$docRoot = rtrim(str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'])), '/');
$projDir = rtrim(str_replace('\\', '/', realpath(__DIR__)), '/'); // __DIR__ ở file này = project root

$baseUri = substr($projDir, strlen($docRoot));
$baseUri = '/' . ltrim($baseUri, '/');
if ($baseUri === '/') {
  // nếu project ở root của web server
  $baseUri = '/';
} else {
  // đảm bảo kết thúc bằng '/'
  $baseUri = rtrim($baseUri, '/') . '/';
}

// URL đầy đủ nếu cần
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'
    || ($_SERVER['SERVER_PORT'] ?? '') == 443) ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$baseUrl = $scheme . '://' . $host . $baseUri;

define('BASE_URI', $baseUri);   // ví dụ '/CoursePro1/' hoặc '/'
define('BASE_URL', $baseUrl);   // ví dụ 'http://192.168.x.x/CoursePro1/'
?>