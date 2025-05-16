<?php
$videoFileName = isset($_GET['file']) ? basename($_GET['file']) : null;

if (!$videoFileName) {
    http_response_code(400);
    die('No video file specified.');
}

$videoBaseDir = '/opt/lampp/htdocs/CoursePro1/videos/';
$absoluteFilePath = $videoBaseDir . $videoFileName;

if (!file_exists($absoluteFilePath) || !is_readable($absoluteFilePath)) {
    http_response_code(404);
    die('Video file not found or not readable.');
}

$mimeType = mime_content_type($absoluteFilePath);
if (!$mimeType) {
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $absoluteFilePath);
    finfo_close($finfo);
    if (!$mimeType) {
        $mimeType = 'video/mp4';
    }
}

header("Content-Type: " . $mimeType);
header("Content-Disposition: inline; filename=\"" . basename($absoluteFilePath) . "\"");
header("Accept-Ranges: bytes");
header("X-Sendfile: " . $absoluteFilePath);

exit;
