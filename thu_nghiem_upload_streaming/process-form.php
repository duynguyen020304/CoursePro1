<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit('Request method must be POST');
}

if (empty($_FILES)) {
    exit('$_FILES bị rỗng - check lại php.ini để enable tùy chọn file_uploads');
}

if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    switch ($_FILES['image']['error']) {
        case UPLOAD_ERR_PARTIAL:
            exit("File upload partial");
            break;
        case UPLOAD_ERR_NO_FILE:
            exit("No file uploaded");
            break;
        case UPLOAD_ERR_EXTENSION:
            exit("File upload stopped by extension");
            break;
        CASE UPLOAD_ERR_FORM_SIZE:
            exit("File upload stopped by form size");
            break;
        case UPLOAD_ERR_INI_SIZE:
            exit("File upload stopped by ini size");
            break;
        case UPLOAD_ERR_NO_TMP_DIR:
            exit("No temporary directory");
            break;
        case UPLOAD_ERR_CANT_WRITE:
            exit("Can't write to disk");
            break;
        default:
            exit("Unknown error");
            break;
    }
}

if ($_FILES['image']['size'] > 8048576) {
    exit("File size is too large");
}

$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime_type = $finfo->file($_FILES['image']['tmp_name']);


$mime_types = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
if (!in_array($_FILES['image']['type'], $mime_types)) {
    exit("File type is not allowed");
}

// Lấy thông tin đường dẫn của file
$pathinfo = pathinfo($_FILES['image']['name']);
$base = $pathinfo['filename'];

// Thay thế ký tự không hợp lệ
$base = preg_replace("/[^\w-]/", "_", $base);

// Tạo UUID
$uuid = substr(str_replace('.', '_', uniqid('', true)), 0, 10);

// Tạo tên file mới có UUID
$filename = $base . '_' . $uuid . '.' . $pathinfo['extension'];

// Đường dẫn đích
$destination = __DIR__ . '/test_upload/' . $filename;

$i = 1;
while (file_exists($destination)) {
    $destination = $base. "($i)" . $pathinfo['extension'];
    $i++;
}

// Di chuyển file
if (!move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
    exit("Failed to move uploaded file");
}

// Lấy tên file đã lưu
$uploaded_filename = $filename;
header("Location: form.html?uploaded_file=" . urlencode($uploaded_filename));
exit;
?>