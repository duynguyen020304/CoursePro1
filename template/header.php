<?php if (session_status() === PHP_SESSION_NONE) { session_start(); } ?>
<nav class="navbar navbar-expand-lg custom-navbar">
    <div class="container-fluid">
        <a class="navbar-brand" href="home.php">Course Online</a>
        <a class="nav-link category-link" href="#">Category</a>

        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <form class="form-inline my-2 my-lg-0 search-form mx-auto">
                <div class="search-input-wrapper">
                    <input class="form-control search-input" type="search"
                        placeholder="Tìm kiếm khóa học, kỹ năng, chủ đề hoặc giảng viên" aria-label="Search">
                    <div class="search-icon-inside">
                        <!-- SVG icon -->
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                            class="bi bi-search" viewBox="0 0 16 16">
                            <path
                                d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0
                              1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0
                              1 1-11 0 5.5 5.5 0 0 1 11 0" />
                        </svg>
                    </div>
                </div>
            </form>

            <ul class="navbar-nav right-nav">
                <li class="nav-item">
                    <a class="nav-link" href="cart.php">
                        <!-- cart icon -->
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                            class="bi bi-cart" viewBox="0 0 16 16">
                            <path
                                d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0
                              1 .485.379L2.89 3H14.5a.5.5 0 0 1
                              .491.592l-1.5 8A.5.5 0 0 1 13
                              12H4a.5.5 0 0 1-.491-.408L2.01
                              3.607 1.61 2H.5a.5.5 0 0
                              1-.5-.5M3.102 4l1.313 7h8.17l1.313-7H3.102zM5
                              12a2 2 0 1 0 0 4 2 2 0 0 0
                              0-4m7 0a2 2 0 1 0 0 4 2 2 0 0
                              0 0-4m-7 1a1 1 0 1 1 0 2 1
                              1 0 0 1 0-2m7 0a1 1 0 1 1 0 2
                              1 1 0 0 1 0-2" />
                        </svg>
                    </a>
                </li>
<?php
// Logic thay đổi header nếu user đã đăng nhập
// Giả sử session lưu user['name'], user['email']
if (isset($_SESSION['user'])) {
    $user = $_SESSION['user'];
    // Tạo avatar text
    $avatar = '';
    $nameParts = explode(' ', $user['name']);
    if (count($nameParts) >= 2) {
        $avatar = strtoupper(mb_substr($nameParts[0], 0, 1, 'UTF-8') . mb_substr($nameParts[1], 0, 1, 'UTF-8'));
    } else {
        $avatar = strtoupper(mb_substr($user['name'], 0, 2, 'UTF-8'));
    }
    ?>
                <li class="nav-item dropdown user-avatar-nav">
                    <a class="nav-link avatar-btn" href="#" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="avatar-circle"><?php echo $avatar; ?></span>
                        <span class="avatar-dot"></span>
                    </a>
                    <ul class="dropdown-menu user-dropdown-menu" aria-labelledby="userDropdown">
                        <li>
                            <div class="dropdown-header text-center">
                                <span class="avatar-circle-big"><?php echo $avatar; ?></span><br>
                                <b><?php echo htmlspecialchars($user['name']); ?></b><br>
                                <span class="text-muted small"><?php echo htmlspecialchars($user['email']); ?></span>
                            </div>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="courses.php">My learning</a></li>
                        <li><a class="dropdown-item" href="cart.php">My cart <span class="badge rounded-pill bg-primary ms-1">1</span></a></li>
                        <li><a class="dropdown-item" href="#">Wishlist</a></li>
                        <li><a class="dropdown-item" href="#">Teach on Udemy</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="#">Notifications</a></li>
                        <li><a class="dropdown-item" href="#">Messages <span class="badge rounded-pill bg-primary ms-1">3</span></a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="#">Account settings</a></li>
                        <li><a class="dropdown-item" href="#">Payment methods</a></li>
                        <li><a class="dropdown-item" href="#">Subscriptions</a></li>
                        <li><a class="dropdown-item" href="#">Udemy credits</a></li>
                        <li><a class="dropdown-item" href="#">Purchase history</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item d-flex align-items-center justify-content-between" href="#">Language <span class="text-muted small">English &nbsp;<i class="bi bi-globe"></i></span></a></li>
                        <li><a class="dropdown-item" href="#">Public profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="logout.php">Log out</a></li>
                    </ul>
                </li>
                <style>
                    /* Thêm CSS để avatar & menu giống mẫu */
                    .avatar-btn { position: relative; cursor: pointer; }
                    .avatar-circle, .avatar-circle-big {
                        display: inline-block;
                        background: #222;
                        color: #fff;
                        font-weight: bold;
                        border-radius: 50%;
                        width: 32px; height: 32px;
                        text-align: center; line-height: 32px;
                        font-size: 1rem;
                        user-select: none;
                    }
                    .avatar-circle-big {
                        width: 50px; height: 50px;
                        font-size: 1.5rem; line-height: 50px;
                    }
                    .avatar-dot {
                        width:10px; height:10px;
                        border-radius:50%;
                        background: #a259ff; border:2px solid #fff;
                        position:absolute; top:2px; right:4px;
                    }
                    .user-dropdown-menu {
                        width: 270px;
                        border-radius: 12px;
                        box-shadow: 0 6px 48px rgba(32, 32, 72, 0.12);
                    }
                    .user-avatar-nav .dropdown-menu {
                        left: auto; right: 0;
                    }
                    .dropdown-header { padding: 16px 10px 10px 10px; }
                    .dropdown-item .badge { font-size: 12px; }
                    .dropdown-divider {
                        margin: 0.4rem 0;
                    }
                </style>
                <script>
                // Đảm bảo dropdown hoạt động với Bootstrap 5
                document.addEventListener("DOMContentLoaded", function() {
                    var dropdownTrigger = document.getElementById("userDropdown");
                    if (dropdownTrigger) {
                        dropdownTrigger.addEventListener("click", function(e) {
                            e.preventDefault();
                            dropdownTrigger.parentElement.classList.toggle("show");
                            var menu = dropdownTrigger.nextElementSibling;
                            if (menu) menu.classList.toggle("show");
                        });
                    }
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
<?php } else { ?>
                <li class="nav-item">
                    <a class="nav-link btn btn-outline-primary" href="signin.php">Sign In</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link btn btn-primary" href="signup.php">Sign Up</a>
                </li>
<?php } ?>
            </ul>
        </div>
    </div>
</nav>
