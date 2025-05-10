<?php
// template/header.php
if (session_status() == PHP_SESSION_NONE) { // Chỉ cần kiểm tra và gọi session_start() một lần
    session_start();
}
?>
<nav class="navbar navbar-expand-lg custom-navbar">
    <div class="container-fluid">
        <a class="navbar-brand" href="home.php">Course Online</a>
        <a class="nav-link category-link" href="#">Category</a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" <?php //Sử dụng data-bs-toggle và data-bs-target cho Bootstrap 5 ?>
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <form class="form-inline my-2 my-lg-0 search-form mx-auto">
                <div class="search-input-wrapper">
                    <input class="form-control search-input" type="search"
                        placeholder="Tìm kiếm khóa học, kỹ năng, chủ đề hoặc giảng viên" aria-label="Search">
                    <div class="search-icon-inside">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                            class="bi bi-search" viewBox="0 0 16 16">
                            <path
                                d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0" />
                        </svg>
                    </div>
                </div>
            </form>

            <ul class="navbar-nav right-nav">
                <li class="nav-item">
                    <a class="nav-link" href="cart.php">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                            class="bi bi-cart" viewBox="0 0 16 16">
                            <path
                                d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .491.592l-1.5 8A.5.5 0 0 1 13 12H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5M3.102 4l1.313 7h8.17l1.313-7H3.102zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4m7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4m-7 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2m7 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2" />
                        </svg>
                    </a>
                </li>
                <?php
                if (isset($_SESSION['user']) && is_array($_SESSION['user']) && isset($_SESSION['user']['name']) && isset($_SESSION['user']['email'])) { // Kiểm tra kỹ hơn biến session
                    $user = $_SESSION['user'];
                    $avatar = '';
                    $nameParts = explode(' ', trim($user['name'])); // Thêm trim để loại bỏ khoảng trắng thừa
                    if (count($nameParts) >= 2) {
                        // Lấy ký tự đầu của từ đầu tiên và từ cuối cùng (phổ biến hơn cho tên Việt Nam)
                        $firstNameInitial = mb_substr($nameParts[0], 0, 1, 'UTF-8');
                        $lastNameInitial = mb_substr(end($nameParts), 0, 1, 'UTF-8');
                        $avatar = strtoupper($firstNameInitial . $lastNameInitial);
                    } elseif (!empty($nameParts[0])) { // Nếu chỉ có một từ
                        $avatar = strtoupper(mb_substr($nameParts[0], 0, 2, 'UTF-8'));
                    } else { // Trường hợp tên trống hoặc không hợp lệ
                        $avatar = '??';
                    }
                ?>
                <li class="nav-item dropdown user-avatar-nav">
                    <a class="nav-link avatar-btn" href="#" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="avatar-circle"><?php echo htmlspecialchars($avatar); ?></span>
                        <span class="avatar-dot"></span>
                    </a>
                    <ul class="dropdown-menu user-dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown"> <?php // Thêm dropdown-menu-end để menu căn phải ?>
                        <li>
                            <div class="dropdown-header text-center">
                                <span class="avatar-circle-big"><?php echo htmlspecialchars($avatar); ?></span><br>
                                <b><?php echo htmlspecialchars($user['name']); ?></b><br>
                                <span class="text-muted small"><?php echo htmlspecialchars($user['email']); ?></span>
                            </div>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="courses.php">My learning</a></li>
                        <li><a class="dropdown-item" href="cart.php">My cart <span class="badge rounded-pill bg-primary ms-1">1</span></a></li>
                        <li><a class="dropdown-item" href="#">Wishlist</a></li>
                        <li><a class="dropdown-item" href="#">Teach on Course Online</a></li> <?php // Sửa tên cho nhất quán ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="#">Notifications</a></li>
                        <li><a class="dropdown-item" href="#">Messages <span class="badge rounded-pill bg-primary ms-1">3</span></a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="#">Account settings</a></li>
                        <li><a class="dropdown-item" href="#">Payment methods</a></li>
                        <li><a class="dropdown-item" href="#">Subscriptions</a></li>
                        <li><a class="dropdown-item" href="#">Course Online credits</a></li> <?php // Sửa tên cho nhất quán ?>
                        <li><a class="dropdown-item" href="#">Purchase history</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item d-flex align-items-center justify-content-between" href="#">Language <span class="text-muted small">English &nbsp;<i class="bi bi-globe"></i></span></a></li>
                        <li><a class="dropdown-item" href="#">Public profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="logout.php">Log out</a></li>
                    </ul>
                </li>
                <style>
                    .avatar-btn { position: relative; cursor: pointer; }
                    .avatar-circle, .avatar-circle-big {
                        display: inline-flex; /* Sử dụng flex để căn giữa tốt hơn */
                        align-items: center;
                        justify-content: center;
                        background: #343a40; /* Màu nền tối hơn một chút */
                        color: #fff;
                        font-weight: bold;
                        border-radius: 50%;
                        width: 32px; height: 32px;
                        font-size: 0.9rem; /* Giảm kích thước font một chút */
                        user-select: none;
                        text-transform: uppercase; /* Đảm bảo chữ hoa */
                    }
                    .avatar-circle-big {
                        width: 50px; height: 50px;
                        font-size: 1.4rem; /* Điều chỉnh font */
                    }
                    .avatar-dot {
                        width:10px; height:10px;
                        border-radius:50%;
                        background: #0d6efd; /* Màu xanh dương của Bootstrap primary */
                        border:2px solid #fff;
                        position:absolute; top:2px; right:4px;
                    }
                    .user-dropdown-menu { /* Đã thêm dropdown-menu-end vào class HTML */
                        width: 280px; /* Có thể tăng chiều rộng một chút */
                        border-radius: 0.5rem; /* Bo góc nhẹ nhàng hơn */
                        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15); /* Box shadow chuẩn của Bootstrap */
                        font-size: 0.95rem; /* Điều chỉnh font cho menu item */
                    }
                    .dropdown-header { padding: 1rem; } /* Tăng padding */
                    .dropdown-item .badge { font-size: 0.75em; }
                </style>
                <?php if (false): // Tạm thời vô hiệu hóa JS tùy chỉnh nếu dùng JS của Bootstrap 5 ?>
                <script>
                // Đảm bảo dropdown hoạt động với Bootstrap 5
                // Nếu bạn đã include JS của Bootstrap 5 (bootstrap.bundle.min.js), đoạn này có thể không cần thiết.
                // Hãy kiểm tra xem dropdown có hoạt động mà không cần đoạn script này không.
                document.addEventListener("DOMContentLoaded", function() {
                    var dropdownTrigger = document.getElementById("userDropdown");
                    if (dropdownTrigger) {
                        // Khởi tạo dropdown của Bootstrap nếu cần
                        // var bsDropdown = new bootstrap.Dropdown(dropdownTrigger); // Cần Bootstrap 5 JS

                        // Hoặc giữ lại logic cũ nếu Bootstrap JS không được dùng/không hoạt động như ý
                        dropdownTrigger.addEventListener("click", function(e) {
                            e.preventDefault();
                            // Logic đóng/mở thủ công có thể xung đột với Bootstrap JS
                            // Xem xét lại nếu bạn dùng Bootstrap JS
                            var parentElement = dropdownTrigger.parentElement;
                            var menu = dropdownTrigger.nextElementSibling;
                            
                            if (parentElement.classList.contains("show")) {
                                parentElement.classList.remove("show");
                                if (menu) menu.classList.remove("show");
                            } else {
                                parentElement.classList.add("show");
                                if (menu) menu.classList.add("show");
                            }
                        });
                    }
                    // Logic đóng dropdown khi click ra ngoài
                    document.addEventListener("click", function(e){
                        var avatarNav = document.querySelector(".user-avatar-nav");
                        if (avatarNav && !avatarNav.contains(e.target)) {
                            avatarNav.classList.remove("show");
                            var menu = avatarNav.querySelector('.dropdown-menu');
                            if (menu) menu.classList.remove("show");
                        }
                    });
                });
                </script>
                <?php endif; ?>
                <?php } else { ?>
                <li class="nav-item">
                    <a class="nav-link btn btn-outline-primary me-2" href="signin.php">Sign In</a> <?php // Thêm me-2 (margin-end) để có khoảng cách ?>
                </li>
                <li class="nav-item">
                    <a class="nav-link btn btn-primary" href="signup.php">Sign Up</a>
                </li>
                <?php } ?>
            </ul>
        </div>
    </div>
</nav>