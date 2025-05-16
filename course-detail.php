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
?>
<?php include('template/head.php'); ?>
<?php include('template/header.php'); ?>
<link href="public/css/course-detail.css" rel="stylesheet">
<!-- === HERO & ASIDE SECTION === -->
<div class="course-hero-bg">
  <div class="course-hero-container">
    <div class="course-hero-main">
      <nav class="course-breadcrumbs" aria-label="Breadcrumb">
        <a href="#" tabindex="0">Development</a><span aria-hidden="true">›</span>
        <a href="#" tabindex="0">Data Science</a><span aria-hidden="true">›</span>
        <a href="#" tabindex="0">Python</a>
      </nav>
      <h1 class="course-hero-title">Python for Data Science and Machine Learning Bootcamp</h1>
      <div class="course-hero-subtitle">
        Learn how to use NumPy, Pandas, Seaborn, Matplotlib, Plotly, Scikit-Learn, Machine Learning, Tensorflow, and more!
      </div>
      <div class="course-hero-meta" role="list">
        <span class="course-badge" role="listitem" aria-label="Bestseller badge">Bestseller</span>
        <span class="course-rating" role="listitem" aria-label="Course rating 4.6 out of 5 stars">
          <span class="course-rating-num">4.6</span>
          <span class="course-stars" aria-hidden="true">
            <svg viewBox="0 0 20 20" width="14" height="14" fill="#f7b500" aria-hidden="true"><path d="M10 15.27L16.18 19l-1.64-7.03L20 7.24l-7.19-.61L10 0 7.19 6.63 0 7.24l5.46 4.73L3.82 19z"></path></svg>
            <svg viewBox="0 0 20 20" width="14" height="14" fill="#f7b500" aria-hidden="true"><path d="M10 15.27L16.18 19l-1.64-7.03L20 7.24l-7.19-.61L10 0 7.19 6.63 0 7.24l5.46 4.73L3.82 19z"></path></svg>
            <svg viewBox="0 0 20 20" width="14" height="14" fill="#f7b500" aria-hidden="true"><path d="M10 15.27L16.18 19l-1.64-7.03L20 7.24l-7.19-.61L10 0 7.19 6.63 0 7.24l5.46 4.73L3.82 19z"></path></svg>
            <svg viewBox="0 0 20 20" width="14" height="14" fill="#f7b500" aria-hidden="true"><path d="M10 15.27L16.18 19l-1.64-7.03L20 7.24l-7.19-.61L10 0 7.19 6.63 0 7.24l5.46 4.73L3.82 19z"></path></svg>
            <svg viewBox="0 0 20 20" width="14" height="14" fill="#f7b500" aria-hidden="true"><path d="M10 15.27L16.18 19l-1.64-7.03L20 7.24l-7.19-.61L10 0 7.19 6.63 0 7.24l5.46 4.73L3.82 19z"></path></svg>
          </span>
        </span>
        <a href="#" class="course-link-reviews" role="listitem">(150,860 ratings)</a>
        <span class="course-students" role="listitem" aria-label="764,815 students enrolled">
          <svg width="16" height="16" fill="none" stroke="#f7b500" stroke-width="2" aria-hidden="true"><circle cx="8" cy="8" r="7"/></svg>764,815 students
        </span>
      </div>
      <div class="course-meta-author">
        Created by <a href="#" class="course-meta-link">Jose Portilla</a>, <a href="#" class="course-meta-link">Pierian Training</a>
      </div>
      <div class="course-meta-date" aria-label="Course last updated and language information">
        <svg width="14" height="14" fill="none" stroke="#999" stroke-width="2" aria-hidden="true"><circle cx="7" cy="7" r="6"/><path d="M7 3v4l3 3"/></svg>
        <span>Last updated 5/2020</span>
        <span>
          ·
          <svg width="15" height="15" fill="none" stroke="#999" stroke-width="1.7" aria-hidden="true"><circle cx="7.5" cy="7.5" r="6.5"/><path d="M7.5 3v3.5l2.5 1.5M5 7.5h5"/></svg>
          English
        </span>
        <span>
          ·
          <svg width="15" height="15" fill="none" stroke="#999" stroke-width="1.7" aria-hidden="true"><rect width="11" height="7.5" x="2" y="4" rx="1.2"/><path d="M4.5 7.25A2 2 0 0 0 9 9.25"/></svg>
          English, Arabic [Auto]
        </span>
      </div>

      <!-- WHAT YOU'LL LEARN - card style -->
      <div class="course-learn-card" role="region" aria-labelledby="learn-title">
        <h2 id="learn-title" class="course-learn-title">What you'll learn</h2>
        <ul class="course-learn-list">
          <li>
            <svg width="20" height="20" fill="none" stroke="#5624d0" stroke-width="3" aria-hidden="true"><polyline points="5 11 9 15 16 6"/></svg>
            Use Python for Data Science and Machine Learning
          </li>
          <li>
            <svg width="20" height="20" fill="none" stroke="#5624d0" stroke-width="3" aria-hidden="true"><polyline points="5 11 9 15 16 6"/></svg>
            Implement Machine Learning Algorithms
          </li>
          <li>
            <svg width="20" height="20" fill="none" stroke="#5624d0" stroke-width="3" aria-hidden="true"><polyline points="5 11 9 15 16 6"/></svg>
            Learn to use Pandas for Data Analysis
          </li>
          <li>
            <svg width="20" height="20" fill="none" stroke="#5624d0" stroke-width="3" aria-hidden="true"><polyline points="5 11 9 15 16 6"/></svg>
            Use Spark for Big Data Analysis
          </li>
          <li>
            <svg width="20" height="20" fill="none" stroke="#5624d0" stroke-width="3" aria-hidden="true"><polyline points="5 11 9 15 16 6"/></svg>
            Learn to use Matplotlib for Python Plotting
          </li>
          <li>
            <svg width="20" height="20" fill="none" stroke="#5624d0" stroke-width="3" aria-hidden="true"><polyline points="5 11 9 15 16 6"/></svg>
            Learn to use Seaborn for statistical plots
          </li>
          <li>
            <svg width="20" height="20" fill="none" stroke="#5624d0" stroke-width="3" aria-hidden="true"><polyline points="5 11 9 15 16 6"/></svg>
            Use SciKit-Learn for Machine Learning Tasks
          </li>
          <li>
            <svg width="20" height="20" fill="none" stroke="#5624d0" stroke-width="3" aria-hidden="true"><polyline points="5 11 9 15 16 6"/></svg>
            K-Means Clustering
          </li>
        </ul>
      </div>

      <!-- COURSE CONTENT -->
      <div class="course-content-card" role="region" aria-labelledby="content-title">
        <div class="course-content-header-row">
          <h2 id="content-title" class="course-content-title">Course content</h2>
          <a class="course-content-expand" href="#" role="button" aria-expanded="false" aria-controls="course-content-accordion">Expand all sections</a>
        </div>
        <div class="course-content-meta" aria-live="polite" aria-atomic="true">27 sections • 165 lectures • 24h 54m total length</div>
        <div class="course-content-accordion" id="course-content-accordion">
          <div class="course-section">
            <button class="course-section-toggle" aria-expanded="true" aria-controls="section-1-content" id="section-1-toggle" onclick="toggleSyllabusSection(this)">
              <span class="course-section-title">Course Introduction</span>
              <span class="course-section-info">3 lectures • 7min</span>
            </button>
            <div class="course-section-content open" id="section-1-content" role="region" aria-labelledby="section-1-toggle">
              <ul class="course-lecture-list">
                <li>
                  <svg width="18" height="18" fill="none" stroke="#5624d0" stroke-width="2" aria-hidden="true"><rect x="4" y="2" width="10" height="14" rx="2"/></svg>
                  <a class="course-lecture-link" href="#">Introduction to the Course</a>
                  <span class="course-lecture-preview">Preview</span>
                  <span class="course-lecture-duration">03:33</span>
                </li>
                <li>
                  <svg width="18" height="18" fill="none" stroke="#5624d0" stroke-width="2" aria-hidden="true"><rect x="4" y="2" width="10" height="14" rx="2"/></svg>
                  <a class="course-lecture-link" href="#">Course Help and Welcome</a>
                  <span class="course-lecture-preview">Preview</span>
                  <span class="course-lecture-duration">00:36</span>
                </li>
                <li>
                  <svg width="18" height="18" fill="none" stroke="#1c1d1f" stroke-width="2" aria-hidden="true"><rect x="2.5" y="4" width="13" height="10" rx="2"/></svg>
                  Course FAQs
                  <span class="course-lecture-duration">03:02</span>
                </li>
              </ul>
            </div>
          </div>
          <div class="course-section">
            <button class="course-section-toggle" aria-expanded="false" aria-controls="section-2-content" id="section-2-toggle" onclick="toggleSyllabusSection(this)">
              <span class="course-section-title">Environment Set-Up</span>
              <span class="course-section-info">1 lecture • 1min</span>
            </button>
            <div class="course-section-content" id="section-2-content" role="region" aria-labelledby="section-2-toggle">
              <ul class="course-lecture-list">
                <li>
                  <svg width="18" height="18" fill="none" stroke="#5624d0" stroke-width="2" aria-hidden="true"><rect x="4" y="2" width="10" height="14" rx="2"/></svg>
                  <a class="course-lecture-link" href="#">Python Environment Setup</a>
                  <span class="course-lecture-preview">Preview</span>
                  <span class="course-lecture-duration">11:14</span>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ASIDE RIGHT -->
    <aside class="course-aside" aria-label="Course purchase options and details">
      <div class="course-aside-imgbox">
        <img src="https://ext.same-assets.com/2787808637/1979837706.jpeg" alt="Course preview image" class="course-aside-img" />
        <div class="course-aside-preview" role="button" tabindex="0" aria-label="Preview this course">
          <svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <circle cx="24" cy="24" r="24" fill="#fff" fill-opacity="0.88"/>
            <polygon points="20,16 34,24 20,32" fill="#5624d0"/>
          </svg>
          <span>Preview this course</span>
        </div>
      </div>
      <div class="course-price" aria-label="Course price">₫2,019,000</div>
      <button class="course-btn course-btn-cart" type="button">Add to cart</button>
      <button class="course-btn course-btn-buy" type="button">Buy now</button>
      <div class="course-guarantee">30-Day Money-Back Guarantee</div>
      <ul class="course-feature-list">
        <li>
          <svg width="18" height="18" fill="none" stroke="#5624d0" stroke-width="2" aria-hidden="true"><rect x="3" y="4" width="12" height="10" rx="2"/><path d="M3 8h12"/></svg>
          25 hours on-demand video
        </li>
        <li>
          <svg width="18" height="18" fill="none" stroke="#5624d0" stroke-width="2" aria-hidden="true"><rect x="4" y="2" width="10" height="14" rx="2"/></svg>
          13 articles
        </li>
        <li>
          <svg width="18" height="18" fill="none" stroke="#5624d0" stroke-width="2" aria-hidden="true"><rect x="3" y="4" width="12" height="10" rx="2"/><path d="M3 8h12"/></svg>
          5 downloadable resources
        </li>
        <li>
          <svg width="18" height="18" fill="none" stroke="#5624d0" stroke-width="2" aria-hidden="true"><rect x="2" y="2" width="14" height="14" rx="3"/></svg>
          Access on mobile and TV
        </li>
        <li>
          <svg width="18" height="18" fill="none" stroke="#5624d0" stroke-width="2" aria-hidden="true"><rect x="4" y="2" width="10" height="14" rx="2"/></svg>
          Full lifetime access
        </li>
        <li>
          <svg width="18" height="18" fill="none" stroke="#5624d0" stroke-width="2" aria-hidden="true"><circle cx="9" cy="9" r="7"/><path d="M9 5v4l3 3"/></svg>
          Certificate of completion
        </li>
      </ul>
      <div class="course-aside-actionlinks">
        <a href="#" tabindex="0">Share</a>
        <a href="#" tabindex="0">Gift this course</a>
        <a href="#" tabindex="0">Apply Coupon</a>
      </div>
      <form class="course-aside-coupon" onsubmit="event.preventDefault(); alert('Coupon applied!');" aria-label="Apply coupon code">
        <input type="text" class="course-aside-input" placeholder="Enter coupon" aria-label="Coupon code" />
        <button type="submit" class="course-btn course-btn-apply">Apply</button>
      </form>
      <div class="course-aside-business" role="region" aria-label="Business training offer">
        <div class="course-aside-business-title">Training 5 or more people?</div>
        <div class="course-aside-business-desc">Get your team access to 27,000+ top courses anytime, anywhere.</div>
        <button class="course-btn course-btn-business" type="button">Try Business Plan</button>
      </div>
    </aside>
  </div>
</div>

<!-- REQUIREMENTS & DESCRIPTION & INSTRUCTORS & FEATURED REVIEW -->
<div class="course-section-main">
  <!-- REQUIREMENTS -->
  <section class="course-section-block" aria-labelledby="requirements-title">
    <h2 id="requirements-title" class="course-section-title">Requirements</h2>
    <ul class="course-req-list">
      <li>
        <svg width="20" height="20" fill="none" stroke="#5624d0" stroke-width="2" aria-hidden="true"><circle cx="10" cy="10" r="9"/><path d="M7 12l3 3 5-5"/></svg>
        Some programming experience
      </li>
      <li>
        <svg width="20" height="20" fill="none" stroke="#5624d0" stroke-width="2" aria-hidden="true"><circle cx="10" cy="10" r="9"/><path d="M7 12l3 3 5-5"/></svg>
        Admin permissions to download files
      </li>
    </ul>
  </section>

  <hr class="course-block-divider" />

  <!-- DESCRIPTION -->
  <section class="course-section-block" aria-labelledby="description-title">
    <h2 id="description-title" class="course-section-title">Description</h2>
    <p>Are you ready to start your path to becoming a Data Scientist!</p>
    <p>This comprehensive course will be your guide to learning how to use the power of Python to analyze data, create beautiful visualizations, and use powerful machine learning algorithms!</p>
    <p>This course is designed for both beginners with some programming experience or experienced developers looking to make the jump to Data Science!</p>
    <a href="#" class="course-desc-more" aria-expanded="false" aria-controls="description-more" onclick="toggleDescription(this); return false;">
      Show more
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#5624d0" stroke-width="2" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg>
    </a>
    <div id="description-more" hidden>
      <p>Additional detailed description content can go here, expanding on the course features, benefits, and what students can expect to learn.</p>
    </div>
  </section>

  <hr class="course-block-divider" />

  <!-- FEATURED REVIEW -->
  <section class="course-section-block" aria-labelledby="featured-review-title">
    <h2 id="featured-review-title" class="course-section-title">Featured review</h2>
    <div class="course-review-box">
      <div class="course-review-avatar">
        <img src="https://ext.same-assets.com/2787808637/2535107833.jpeg" alt="Avatar of Jerry M." />
      </div>
      <div class="course-review-content">
        <div class="course-review-author">Jerry M.</div>
        <div class="course-review-meta">
          5 courses • 3 reviews<span class="dot" aria-hidden="true">·</span>
          <span class="course-review-rating" aria-label="4 stars rating">
            <svg viewBox="0 0 20 20" width="15" height="15" fill="#f69c08" aria-hidden="true"><path d="M10 15.27L16.18 19l-1.64-7.03L20 7.24l-7.19-.61L10 0 7.19 6.63 0 7.24l5.46 4.73L3.82 19z"></path></svg>
            4 years ago
          </span>
        </div>
        <div class="course-review-desc">
          This was my first course in data science. All of the explanations were clear, and you can tell the instructor truly cares about software education. It covers all the most important topics in machine learning, and gives just enough theoretical knowledge to have some basic understanding of the algorithms behind the scenes.
        </div>
      </div>
    </div>
  </section>

  <hr class="course-block-divider" />

  <!-- INSTRUCTORS SECTION -->
  <section class="course-section-block" aria-labelledby="instructors-title">
    <h2 id="instructors-title" class="course-section-title">Instructors</h2>
    <div class="course-instructors-list">
      <div class="course-instructor-box">
        <a href="#" class="course-instructor-name">Jose Portilla</a>
        <span class="course-instructor-title">Head of Data Science at Pierian Training</span>
        <div class="course-instructor-profile">
          <div class="course-instructor-avt">
            <img src="https://ext.same-assets.com/2787808637/2535107833.jpeg" alt="Jose Portilla" />
          </div>
          <div class="course-instructor-meta">
            <div><span class="inst-star" aria-label="Instructor rating 4.6 stars">★ 4.6</span> Instructor Rating</div>
            <div><svg width="16" height="16" fill="none" stroke="#6a6f73" stroke-width="1.5" aria-hidden="true"><circle cx="8" cy="8" r="7"/></svg> 1,263,843 Reviews</div>
            <div><svg width="16" height="16" fill="none" stroke="#6a6f73" stroke-width="1.5" aria-hidden="true"><circle cx="8" cy="8" r="7"/><path d="M4 14l8-8"/></svg> 4,237,490 Students</div>
            <div><svg width="16" height="16" fill="none" stroke="#6a6f73" stroke-width="1.5" aria-hidden="true"><circle cx="8" cy="8" r="7"/><path d="M8 4v8"/></svg> 87 Courses</div>
          </div>
        </div>
        <div class="course-instructor-bio">
          Jose Marcial Portilla has a BS and MS in Mechanical Engineering from Santa Clara University and years of experience as a professional instructor and trainer for Data Science, Machine Learning and Python Programming. He has publications and patents in various fields such as microfluidics, materials science, and data science. Over the course of his career he has developed a skill set in analyzing data and he hopes to use his experience in teaching and data science to help other people learn the power of programming.
        </div>
      </div>
    </div>
  </section>
</div>

<script src="public/js/course-detail.js"></script>
<?php include('template/footer.php'); ?>
