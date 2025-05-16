<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Determine protocol, host, and application root path
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http');
$host = $_SERVER['HTTP_HOST'];
$script_path = $_SERVER['SCRIPT_NAME'];
$path_parts = explode('/', ltrim($script_path, '/'));
$app_root_directory_name = $path_parts[0];
$app_root_path_relative = '/' . $app_root_directory_name;
$known_app_subdir_markers = ['/admin/', '/api/', '/includes/'];
$found_marker = false;

// Attempt to find a known subdirectory marker to accurately determine the app root
foreach ($known_app_subdir_markers as $marker) {
    $pos = strpos($script_path, $marker);
    if ($pos !== false) {
        $app_root_path_relative = substr($script_path, 0, $pos);
        $found_marker = true;
        break;
    }
}

// Fallback if no marker is found, or adjust if script is in the root
if (!$found_marker) {
    $app_root_path_relative = dirname($script_path);
    if ($app_root_path_relative === '/' && $script_path !== '/') {
        // Script is in a subdirectory of the web root, but dirname gives '/'
        // This case might need specific handling if app is not in a top-level dir
        $app_root_path_relative = ''; // Assuming app is at the web root if script is like /index.php
    } elseif ($app_root_path_relative === '/' && $script_path === '/') {
        // Script is /index.php at the web root
        $app_root_path_relative = '';
    }
}

// Ensure $app_root_path_relative does not end with a slash unless it's the root itself
if ($app_root_path_relative !== '/' && $app_root_path_relative !== '' && substr($app_root_path_relative, -1) === '/') {
    $app_root_path_relative = rtrim($app_root_path_relative, '/');
}

// Define the base URL for API calls
define('API_BASE', $protocol . '://' . $host . $app_root_path_relative . '/api');

/**
 * Makes a call to the API.
 *
 * @param string $endpoint The API endpoint to call (e.g., '/users').
 * @param string $method The HTTP method (GET, POST, PUT, DELETE). Defaults to 'GET'.
 * @param array $payload Data to send with the request (for POST, PUT). For GET, it's used as query parameters.
 * @return array The API response decoded as an associative array, including 'http_status_code' and 'success'.
 */
function callApi(string $endpoint, string $method = 'GET', array $payload = []): array
{
    $url = API_BASE . '/' . ltrim($endpoint, '/');
    $methodUpper = strtoupper($method); // Standardize method to uppercase

    // If it's a GET request and there's a payload, build a query string
    if ($methodUpper === 'GET' && !empty($payload)) {
        $url .= '?' . http_build_query($payload);
    }

    // Initialize headers string
    $headers = "Content-Type: application/json; charset=utf-8\r\n" .
               "Accept: application/json\r\n";

    // Get token from session if available
    $token = $_SESSION['user']['token'] ?? null;

    // If token exists, add Authorization header
    if ($token) {
        $headers .= "Authorization: Bearer " . $token . "\r\n";
    }

    // Set up stream context options
    $options = [
        'http' => [
            'method'        => $methodUpper,
            'header'        => $headers, // Use the updated headers string
            'ignore_errors' => true, // Allows fetching content even on HTTP errors (4xx, 5xx)
        ]
    ];

    // Add 'content' (body) only for non-GET methods and if payload is present
    if ($methodUpper !== 'GET') {
        if (!empty($payload)) {
            $options['http']['content'] = json_encode($payload);
        } else if (in_array($methodUpper, ['POST', 'PUT'])) {
            // Send an empty JSON object if no payload for POST/PUT, as some APIs expect it
            $options['http']['content'] = '{}';
        }
    }

    // Create stream context and perform the request
    $context  = stream_context_create($options);
    $response = @file_get_contents($url, false, $context); // Use @ to suppress warnings on failure
    $result   = json_decode($response, true);

    // Determine HTTP status code from response headers
    $status_code = 500; // Default to server error if headers are not available
    if (isset($http_response_header[0])) { // $http_response_header is a special variable populated by PHP
        preg_match('{HTTP\/\S*\s(\d{3})}', $http_response_header[0], $match);
        if (isset($match[1])) {
            $status_code = intval($match[1]);
        }
    }

    // If $result is not an array (e.g., JSON decode failed or empty response), return a standard error structure
    if (!is_array($result)) {
        return [
            'success' => false,
            'message' => 'Invalid API response or failed to decode JSON.',
            'data' => null,
            'raw_response' => $response, // Include raw response for debugging
            'http_status_code' => $status_code
        ];
    }

    // Ensure 'http_status_code' and 'success' keys are present in the final result
    $result['http_status_code'] = $status_code;
    if (!isset($result['success'])) {
        $result['success'] = ($status_code >= 200 && $status_code < 300); // Infer success from status code if not set
    }
    return $result;
}

// Example user data (Status field removed)
$exampleUsers = [
    ['UserID' => 1, 'FullName' => 'Nguyễn Văn Admin', 'Email' => 'admin@example.com', 'Role' => 'Admin', 'JoinDate' => '2024-01-10'],
    ['UserID' => 2, 'FullName' => 'Trần Thị Học Viên', 'Email' => 'hocvien@example.com', 'Role' => 'User', 'JoinDate' => '2024-03-15'],
    ['UserID' => 3, 'FullName' => 'Lê Văn Instructor', 'Email' => 'instructor@example.com', 'Role' => 'Instructor', 'JoinDate' => '2024-02-01'],
    ['UserID' => 4, 'FullName' => 'Phạm Thị B', 'Email' => 'phamthib@example.com', 'Role' => 'User', 'JoinDate' => '2025-05-01'],
];
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <title>Quản lý Người dùng</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="css/base_dashboard.css" rel="stylesheet">
    <link href="css/admin_style.css" rel="stylesheet">
    <style>
        /* Custom styles for action buttons */
        .action-buttons .btn {
            margin-right: 0.25rem; /* Spacing between buttons */
        }
        .action-buttons .btn:last-child {
            margin-right: 0; /* No margin for the last button */
        }
        /* Removed status-badge style as status is removed */
    </style>
</head>

<body>
    <?php include('template/dashboard.php'); // Include dashboard template ?>

    <div class="main-content">
        <div class="container-fluid py-4">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="mb-0">Quản lý Người dùng</h3>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#userModal">
                    <i class="bi bi-person-plus-fill me-1"></i> Thêm người dùng
                </button>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <form class="row g-3 align-items-center">
                        <div class="col-md-6"> <label for="filterName" class="visually-hidden">Tên hoặc Email</label>
                            <input type="text" class="form-control" id="filterName" placeholder="Tìm theo tên hoặc email...">
                        </div>
                        <div class="col-md-4"> <label for="filterRole" class="visually-hidden">Vai trò</label>
                            <select class="form-select" id="filterRole">
                                <option selected value="">Tất cả vai trò</option>
                                <option value="Admin">Admin</option>
                                <option value="Instructor">Giảng viên</option>
                                <option value="User">Học viên</option>
                            </select>
                        </div>
                        <div class="col-md-2"> <button type="submit" class="btn btn-outline-secondary w-100">
                                <i class="bi bi-funnel-fill me-1"></i> Lọc
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Họ và Tên</th>
                            <th>Email</th>
                            <th>Vai trò</th>
                            <th>Ngày tham gia</th>
                            <th class="text-end">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Check if there are users to display
                        if (!empty($exampleUsers)) {
                            foreach ($exampleUsers as $i => $user) {
                                // Status related logic and class removed
                                echo "<tr>";
                                echo "<td>".($i+1)."</td>"; // Displaying sequential ID for simplicity
                                echo "<td>" . htmlspecialchars($user['FullName']) . "</td>";
                                echo "<td>" . htmlspecialchars($user['Email']) . "</td>";
                                echo "<td>" . htmlspecialchars($user['Role']) . "</td>";
                                echo "<td>" . date('d/m/Y', strtotime($user['JoinDate'])) . "</td>";
                                // Status cell (<td>) removed
                                echo "<td class='text-end action-buttons'>
                                        <button class='btn btn-sm btn-outline-primary edit-user' data-id='{$user['UserID']}' data-bs-toggle='modal' data-bs-target='#userModal' title='Sửa'>
                                            <i class='bi bi-pencil-square'></i>
                                        </button>
                                        <button class='btn btn-sm btn-outline-danger delete-user' data-id='{$user['UserID']}' title='Xóa'>
                                            <i class='bi bi-trash3-fill'></i>
                                        </button>
                                      </td>";
                                echo "</tr>";
                            }
                        } else {
                            // Message if no users are found, colspan adjusted
                            echo "<tr><td colspan='6' class='text-center'>Không tìm thấy người dùng nào.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <nav aria-label="User pagination" class="mt-4 d-flex justify-content-center">
                <ul class="pagination">
                    <li class="page-item disabled"><a class="page-link" href="#">Trước</a></li>
                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                    <li class="page-item"><a class="page-link" href="#">2</a></li>
                    <li class="page-item"><a class="page-link" href="#">Sau</a></li>
                </ul>
            </nav>

        </div>
    </div>

    <div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <form id="userForm" method="post" action="user_save.php">
                    <div class="modal-header">
                        <h5 class="modal-title" id="userModalLabel">Thêm Người dùng</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="UserID" id="modalUserID">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="modalFullName" class="form-label">Họ và Tên <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="modalFullName" name="FullName" required>
                            </div>
                            <div class="col-md-6">
                                <label for="modalEmail" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="modalEmail" name="Email" required>
                            </div>

                            <div class="col-md-6">
                                <label for="modalPassword" class="form-label">Mật khẩu</label>
                                <input type="password" class="form-control" id="modalPassword" name="Password">
                                <small class="form-text text-muted" id="passwordHelp">Để trống nếu không muốn thay đổi mật khẩu.</small>
                            </div>
                            <div class="col-md-6">
                                <label for="modalRole" class="form-label">Vai trò <span class="text-danger">*</span></label>
                                <select class="form-select" id="modalRole" name="Role" required>
                                    <option value="User" selected>Học viên (User)</option>
                                    <option value="Instructor">Giảng viên (Instructor)</option>
                                    <option value="Admin">Quản trị viên (Admin)</option>
                                </select>
                            </div>
                            </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-save-fill me-1"></i> Lưu người dùng</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Bootstrap Modal
            const userModal = new bootstrap.Modal(document.getElementById('userModal'));
            // Get modal elements
            const userModalLabel = document.getElementById('userModalLabel');
            const userForm = document.getElementById('userForm');
            const modalUserID = document.getElementById('modalUserID');
            const modalFullName = document.getElementById('modalFullName');
            const modalEmail = document.getElementById('modalEmail');
            const modalPassword = document.getElementById('modalPassword');
            const passwordHelpText = document.getElementById('passwordHelp');
            const modalRole = document.getElementById('modalRole');
            // modalStatus element reference removed

            // Event listener for "Add User" button
            document.querySelector('button[data-bs-target="#userModal"]').addEventListener('click', function() {
                userModalLabel.textContent = 'Thêm Người dùng mới';
                userForm.reset(); // Reset form fields
                modalUserID.value = ''; // Clear user ID
                passwordHelpText.textContent = 'Mật khẩu là bắt buộc khi thêm mới.';
                modalPassword.required = true; // Password is required for new users
            });

            // Event listeners for "Edit User" buttons
            document.querySelectorAll('.edit-user').forEach(button => {
                button.addEventListener('click', function() {
                    userModalLabel.textContent = 'Chỉnh sửa Người dùng';
                    const userId = this.getAttribute('data-id');
                    modalUserID.value = userId;
                    passwordHelpText.textContent = 'Để trống nếu không muốn thay đổi mật khẩu.';
                    modalPassword.required = false; // Password is not required when editing (unless changing it)

                    // Find the user data from the PHP-generated exampleUsers array
                    // In a real application, you would fetch this via AJAX: fetchUserDetails(userId).then(...)
                    const userToEdit = <?php echo json_encode($exampleUsers); ?>.find(u => u.UserID == userId);
                    
                    if (userToEdit) {
                        modalFullName.value = userToEdit.FullName;
                        modalEmail.value = userToEdit.Email;
                        modalRole.value = userToEdit.Role;
                        // modalStatus.value assignment removed
                    }
                    userModal.show(); // Show the modal
                });
            });

            // Event listeners for "Delete User" buttons
            document.querySelectorAll('.delete-user').forEach(button => {
                button.addEventListener('click', function() {
                    const userId = this.getAttribute('data-id');
                    // Get user name from the table row for confirmation message
                    const userName = this.closest('tr').querySelector('td:nth-child(2)').textContent;
                    if (confirm(`Bạn có chắc chắn muốn xóa người dùng "${userName}" (ID: ${userId}) không?`)) {
                        // Placeholder for actual delete logic (e.g., AJAX call to server)
                        console.log(`Yêu cầu xóa người dùng ID: ${userId}. (Cần triển khai logic xóa thực tế)`);
                        alert(`Đã yêu cầu xóa người dùng ID: ${userId}. (Cần triển khai logic xóa thực tế)`);
                        // Optionally, remove the row from the table on successful deletion:
                        // this.closest('tr').remove(); 
                    }
                });
            });
        });
    </script>
</body>
</html>
