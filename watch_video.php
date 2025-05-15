<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http');
$host = $_SERVER['HTTP_HOST'];
$script_path = $_SERVER['SCRIPT_NAME'];
$path_parts = explode('/', ltrim($script_path, '/'));
$app_root_directory_name = $path_parts[0];
$app_root_path_relative = '/' . $app_root_directory_name;
$known_app_subdir_markers = ['/admin/', '/api/', '/includes/'];
$found_marker = false;

foreach ($known_app_subdir_markers as $marker) {
    $pos = strpos($script_path, $marker);
    if ($pos !== false) {
        $app_root_path_relative = substr($script_path, 0, $pos);
        $found_marker = true;
        break;
    }
}

if (!$found_marker) {
    $app_root_path_relative = dirname($script_path);
    if ($app_root_path_relative === '/' && $script_path !== '/') {
        $app_root_path_relative = '';
    } elseif ($app_root_path_relative === '/' && $script_path === '/') {
        $app_root_path_relative = '';
    }
}

if ($app_root_path_relative !== '/' && $app_root_path_relative !== '' && substr($app_root_path_relative, -1) === '/') {
    $app_root_path_relative = rtrim($app_root_path_relative, '/');
}

define('API_BASE', $protocol . '://' . $host . $app_root_path_relative . '/api');

function callApi(string $endpoint, string $method = 'GET', array $payload = []): array
{
    $url = API_BASE . '/' . ltrim($endpoint, '/');
    $methodUpper = strtoupper($method); // Chuyển method thành chữ hoa để xử lý nhất quán

    // Nếu là GET và có $payload, xây dựng query string
    if ($methodUpper === 'GET' && !empty($payload)) {
        $url .= '?' . http_build_query($payload);
    }

    // Khởi tạo chuỗi header
    $headers = "Content-Type: application/json; charset=utf-8\r\n" .
        "Accept: application/json\r\n";

    // Lấy token từ session nếu có
    $token = $_SESSION['user']['token'] ?? null;

    // Nếu có token, thêm header Authorization
    if ($token) {
        $headers .= "Authorization: Bearer " . $token . "\r\n";
    }

    $options = [
        'http' => [
            'method'        => $methodUpper,
            'header'        => $headers, // Sử dụng chuỗi headers đã được cập nhật
            'ignore_errors' => true,
        ]
    ];

    // Chỉ thêm 'content' (body) cho các method không phải GET và có $payload
    if ($methodUpper !== 'GET') {
        if (!empty($payload)) {
            $options['http']['content'] = json_encode($payload);
        } else if (in_array($methodUpper, ['POST', 'PUT'])) {
            // Gửi một đối tượng JSON rỗng nếu không có payload cho POST/PUT
            $options['http']['content'] = '{}';
        }
    }

    $context  = stream_context_create($options);
    $response = @file_get_contents($url, false, $context);
    $result   = json_decode($response, true);

    $status_code = 500; // Mặc định là lỗi server nếu không lấy được header
    if (isset($http_response_header[0])) {
        preg_match('{HTTP\/\S*\s(\d{3})}', $http_response_header[0], $match);
        if (isset($match[1])) {
            $status_code = intval($match[1]);
        }
    }

    // Nếu $result không phải là mảng (ví dụ: lỗi decode JSON), trả về cấu trúc lỗi chuẩn
    if (!is_array($result)) {
        return [
            'success' => false,
            'message' => 'Invalid API response or failed to decode JSON.',
            'data' => null,
            'raw_response' => $response, // Giữ lại raw response để debug
            'http_status_code' => $status_code
        ];
    }

    // Đảm bảo có 'http_status_code' và 'success' trong kết quả trả về
    $result['http_status_code'] = $status_code;
    if (!isset($result['success'])) {
        $result['success'] = ($status_code >= 200 && $status_code < 300);
    }
    return $result;
}
$courseTitle = "Semiconductor Essentials: A practical guide to understanding core semiconductor components";
$instructorName = "Barron Stonne";
$instructorTitle = "Electrical engineer & Graphic Designer";
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo htmlspecialchars($courseTitle); ?> | Udemy Clone</title>
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
    rel="stylesheet"
  >
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css"
    rel="stylesheet"
  >
  <link rel="stylesheet" href="public/css/watch_video.css">
</head>
<body>
  <header class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top shadow-sm">
    <div class="container-fluid">
      <a class="navbar-brand fw-bold" href="home.php">COURSE ONLINE</a>
      <div class="course-title-header text-light d-none d-lg-block text-truncate px-3">
        <?php echo htmlspecialchars($courseTitle); ?>
      </div>
      <button class="btn btn-outline-light d-lg-none ms-auto me-2" type="button" id="openCourseContentSidebarMobile">
          <i class="bi bi-list-nested"></i> <span class="d-none d-sm-inline">Nội dung</span>
      </button>
      <div class="ms-auto d-flex align-items-center d-none d-lg-flex">
        <div class="dropdown me-2">
          <button class="btn btn-outline-light btn-sm dropdown-toggle" type="button" id="progressDropdown" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-check-circle"></i> Tiến độ của bạn
          </button>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="progressDropdown">
            <li><a class="dropdown-item" href="#">Đã hoàn thành: 0/19</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="#">Đánh dấu hoàn thành</a></li>
          </ul>
        </div>
        <button class="btn btn-outline-light btn-sm" type="button">
          <i class="bi bi-share"></i> Chia sẻ
        </button>
      </div>
    </div>
  </header>

  <div class="course-main-layout">
    <div class="video-player-area">
      <div class="video-container ratio ratio-16x9">
        <video controls poster="poster.jpg">
          <source src="videos/206309_medium.mp4" type="video/mp4">
          Trình duyệt của bạn không hỗ trợ thẻ video.
        </video>
      </div>

      <div class="video-bottom-tabs-container container-fluid px-lg-0">
         <ul class="nav nav-tabs mt-3" id="videoTab" role="tablist">
          <li class="nav-item" role="presentation">
            <button
              class="nav-link active"
              id="overview-tab"
              data-bs-toggle="tab"
              data-bs-target="#overview"
              type="button" role="tab"
            >Tổng quan</button>
          </li>
          <li class="nav-item" role="presentation">
            <button
              class="nav-link"
              id="notes-tab"
              data-bs-toggle="tab"
              data-bs-target="#notes"
              type="button" role="tab"
            >Ghi chú</button>
          </li>
          <li class="nav-item" role="presentation">
            <button
              class="nav-link"
              id="announcements-tab"
              data-bs-toggle="tab"
              data-bs-target="#announcements"
              type="button" role="tab"
            >Thông báo</button>
          </li>
          <li class="nav-item" role="presentation">
            <button
              class="nav-link"
              id="reviews-tab"
              data-bs-toggle="tab"
              data-bs-target="#reviews"
              type="button" role="tab"
            >Đánh giá</button>
          </li>
          <li class="nav-item" role="presentation">
            <button
              class="nav-link"
              id="learning-tools-tab"
              data-bs-toggle="tab"
              data-bs-target="#learning-tools"
              type="button" role="tab"
            >Công cụ học tập</button>
          </li>
        </ul>

        <div class="tab-content p-3 p-lg-4" id="videoTabContent">
          <div class="tab-pane fade show active" id="overview" role="tabpanel">
            <h2 class="mb-3 course-title-in-overview">
              <?php echo htmlspecialchars($courseTitle); ?>
            </h2>
            <div class="d-flex flex-wrap align-items-center mb-3">
              <div class="me-4 rating-stars">
                <i class="bi bi-star-fill"></i>
                <i class="bi bi-star-fill"></i>
                <i class="bi bi-star-fill"></i>
                <i class="bi bi-star-fill"></i>
                <i class="bi bi-star-half"></i>
                <span class="ms-2">4.9</span>
              </div>
              <div class="me-4"><strong>238</strong> Học viên</div>
              <div class="me-4"><strong>2 giờ</strong> Tổng cộng</div>
               <div class="me-4">Cập nhật lần cuối: <strong>Tháng 4, 2025</strong></div>
            </div>
            <div class="row mb-4">
              <div class="col-md-6">
                <h6>Chi tiết khóa học</h6>
                <ul class="list-unstyled">
                  <li><strong>Trình độ:</strong> Mọi cấp độ</li>
                  <li><strong>Ngôn ngữ:</strong> English</li>
                  <li><strong>Phụ đề:</strong> Có</li>
                  <li><strong>Bài giảng:</strong> 19</li>
                </ul>
              </div>
            </div>
            <h5>Mô tả</h5>
            <p>
              Strengthen your foundation in electronic circuit design by learning how to work with
              essential semiconductor components. This comprehensive course walks you through the practical
              use of three fundamental building blocks in electronics: diodes, transistors, and operational
              amplifiers (op-amps). Through hands-on examples, you'll understand how to use diodes effectively
              in your own circuit designs.
            </p>
            <h5 class="mt-4">Giảng viên</h5>
            <div class="d-flex align-items-start bg-light p-3 rounded shadow-sm">
              <img
                src="instructor.jpg"
                alt="Instructor"
                class="rounded-circle me-3"
                width="64" height="64"
              >
              <div>
                <h6 class="mb-0"><?php echo htmlspecialchars($instructorName); ?></h6>
                <small class="text-muted">
                  <?php echo htmlspecialchars($instructorTitle); ?>
                </small>
                <p class="mt-2 mb-0">
                  I am both an electrical engineer and designer, with over 15 years experience working
                  in both fields. I enjoy educating others and sharing my passion for technology and design.
                </p>
              </div>
            </div>
          </div>

          <div class="tab-pane fade" id="notes" role="tabpanel"><p>Nội dung ghi chú...</p></div>
          <div class="tab-pane fade" id="announcements" role="tabpanel"><p>Nội dung thông báo...</p></div>
          <div class="tab-pane fade" id="reviews" role="tabpanel"><p>Nội dung đánh giá...</p></div>
          <div class="tab-pane fade" id="learning-tools" role="tabpanel"><p>Nội dung công cụ học tập...</p></div>
        </div>
      </div>
    </div>

    <aside class="course-content-sidebar">
      <div class="sidebar-header d-flex justify-content-between align-items-center p-3">
        <h5 class="mb-0">Nội dung khóa học</h5>
        <button class="btn-close d-lg-none" type="button" id="closeCourseContentSidebarMobile" aria-label="Close"></button>
      </div>
      <div class="accordion accordion-flush" id="courseContentAccordion">
        <div class="accordion-item">
          <h2 class="accordion-header" id="headingOne">
            <button
              class="accordion-button"
              type="button"
              data-bs-toggle="collapse"
              data-bs-target="#sectionOne"
              aria-expanded="true"
            >
              Section 1: Introduction
              <small class="text-muted ms-auto lectures-count">0/2 | 3min</small>
            </button>
          </h2>
          <div
            id="sectionOne"
            class="accordion-collapse collapse show"
            aria-labelledby="headingOne"
            data-bs-parent="#courseContentAccordion"
          >
            <div class="accordion-body">
              <ul class="list-unstyled lesson-list">
                <li><a href="#" class="active-lesson"><i class="bi bi-play-circle-fill me-2"></i>1. Introduction <small class="text-muted float-end">2min</small></a></li>
                <li><a href="#"><i class="bi bi-play-circle me-2"></i>2. Getting started <small class="text-muted float-end">2min</small></a></li>
              </ul>
            </div>
          </div>
        </div>
        <div class="accordion-item">
          <h2 class="accordion-header" id="headingTwo">
            <button
              class="accordion-button collapsed"
              type="button"
              data-bs-toggle="collapse"
              data-bs-target="#sectionTwo"
            >
              Section 2: Diodes <small class="text-muted ms-auto lectures-count">0/3 | 21min</small>
            </button>
          </h2>
          <div
            id="sectionTwo"
            class="accordion-collapse collapse"
            aria-labelledby="headingTwo"
            data-bs-parent="#courseContentAccordion"
          >
            <div class="accordion-body">
              <ul class="list-unstyled lesson-list">
                <li><a href="#"><i class="bi bi-play-circle me-2"></i>PN Junction <small class="text-muted float-end">5min</small></a></li>
                <li><a href="#"><i class="bi bi-play-circle me-2"></i>Zener Diode <small class="text-muted float-end">8min</small></a></li>
                <li><a href="#"><i class="bi bi-play-circle me-2"></i>Schottky Diode <small class="text-muted float-end">8min</small></a></li>
              </ul>
            </div>
          </div>
        </div>
        <div class="accordion-item">
          <h2 class="accordion-header" id="headingThree">
            <button
              class="accordion-button collapsed"
              type="button"
              data-bs-toggle="collapse"
              data-bs-target="#sectionThree"
            >
              Section 3: Transistors <small class="text-muted ms-auto lectures-count">0/4 | 30min</small>
            </button>
          </h2>
          <div
            id="sectionThree"
            class="accordion-collapse collapse"
            aria-labelledby="headingThree"
            data-bs-parent="#courseContentAccordion"
          >
            <div class="accordion-body">
               <ul class="list-unstyled lesson-list">
                <li><a href="#"><i class="bi bi-play-circle me-2"></i>BJT Introduction <small class="text-muted float-end">7min</small></a></li>
                <li><a href="#"><i class="bi bi-play-circle me-2"></i>FET Introduction <small class="text-muted float-end">8min</small></a></li>
                <li><a href="#"><i class="bi bi-play-circle me-2"></i>MOSFET Basics <small class="text-muted float-end">8min</small></a></li>
                 <li><a href="#"><i class="bi bi-play-circle me-2"></i>Transistor as a Switch <small class="text-muted float-end">7min</small></a></li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </aside>
  </div>

  <script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
  ></script>
  <script src="public/js/watch_video.js"></script>
</body>
</html>