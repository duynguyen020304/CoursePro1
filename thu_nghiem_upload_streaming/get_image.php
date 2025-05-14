<?php
if (isset($_GET['file'])) {
    $filename = basename($_GET['file']); // Ngăn chặn path traversal
    $filepath = __DIR__ . '/test_upload/' . $filename;

    if (file_exists($filepath)) {
        // Xác định MIME type của ảnh
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $filepath);
        finfo_close($finfo);

        // Kiểm tra xem có phải là ảnh không (tùy chọn, nhưng nên có)
        $allowed_mime_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (in_array($mime_type, $allowed_mime_types)) {
            header('Content-Type: ' . $mime_type);
            header('Content-Length: ' . filesize($filepath));
            readfile($filepath);
            exit;
        } else {
            http_response_code(403); // Forbidden
            echo "Tập tin không phải là định dạng ảnh được phép.";
            exit;
        }
    } else {
        http_response_code(404); // Not Found
        echo "Ảnh không tồn tại.";
        exit;
    }
} else {
    http_response_code(400); // Bad Request
    echo "Không có tên file được cung cấp.";
    exit;
}
?>