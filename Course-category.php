<?php
include('template/head.php'); 
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang Khóa Học ChatGPT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="public/css/course-category.css" rel="stylesheet">
</head>
<body class="bg-light">

    <?php include('template/header.php'); // Include file header.php ?>

    <main class="container py-4 py-md-5">
        <section class="bg-white p-3 p-md-4 rounded shadow-sm mb-4 mb-md-5">
            <h1 class="display-5 display-md-4 fw-bold text-dark mb-3">Khóa học ChatGPT</h1>
            <p class="text-muted mb-4 fs-6 fs-md-5">
                Các khóa học ChatGPT dạy mô hình AI của OpenAI để tạo văn bản và hiểu ngôn ngữ tự nhiên, bao gồm xử lý ngôn ngữ tự nhiên, AI đàm thoại và tinh chỉnh mô hình. Khám phá ChatGPT cho các dự án dựa trên AI.
            </p>
            <div class="row row-cols-2 row-cols-sm-4 g-3 mb-4 text-center">
                <div class="col">
                    <p class="h2 fw-bold text-custom-purple">4,493,431</p>
                    <p class="small text-muted">Số người học</p>
                </div>
                <div class="col">
                    <p class="h2 fw-bold text-custom-purple">1,726</p>
                    <p class="small text-muted">Số khóa học</p>
                </div>
                <div class="col">
                    <p class="h2 fw-bold text-custom-purple">1,322 <i class="fas fa-info-circle small text-muted"></i></p>
                    <p class="small text-muted">Số bài thực hành</p>
                </div>
                <div class="col">
                    <p class="h2 fw-bold text-custom-purple">4.5 <i class="fas fa-star text-warning"></i></p>
                    <p class="small text-muted">Đánh giá trung bình</p>
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <span class="text-dark fw-semibold me-2">Liên quan:</span>
                <button class="btn btn-outline-secondary btn-sm rounded-pill py-1 px-2">IT & Software</button>
                <button class="btn btn-outline-secondary btn-sm rounded-pill py-1 px-2">Business</button>
                <button class="btn btn-outline-secondary btn-sm rounded-pill py-1 px-2">Other IT & Software</button>
                <button class="btn btn-outline-secondary btn-sm rounded-pill py-1 px-2">Other Office Productivity</button>
            </div>
        </section>

        <section class="row g-4">
            <aside id="filterSidebar" class="col-md-4 col-lg-3 bg-white p-3 p-md-4 rounded shadow-sm h-auto d-none d-md-block">
                <h2 class="h4 fw-semibold text-dark mb-3">Bộ lọc</h2>
                <div class="filters-container">
                    <div class="mb-3">
                        <h3 class="h6 fw-medium text-dark mb-2">Đánh giá</h3>
                        <ul class="list-unstyled small text-muted">
                            <li class="form-check mb-1">
                                <input class="form-check-input" type="radio" name="rating" value="4.5" id="rating4.5">
                                <label class="form-check-label" for="rating4.5">4.5 sao trở lên</label>
                            </li>
                            <li class="form-check mb-1">
                                <input class="form-check-input" type="radio" name="rating" value="4.0" id="rating4.0">
                                <label class="form-check-label" for="rating4.0">4.0 sao trở lên</label>
                            </li>
                            <li class="form-check mb-1">
                                <input class="form-check-input" type="radio" name="rating" value="3.5" id="rating3.5">
                                <label class="form-check-label" for="rating3.5">3.5 sao trở lên</label>
                            </li>
                            <li class="form-check mb-1">
                                <input class="form-check-input" type="radio" name="rating" value="3.0" id="rating3.0">
                                <label class="form-check-label" for="rating3.0">3.0 sao trở lên</label>
                            </li>
                        </ul>
                    </div>
                    <hr class="my-3">
                    <div class="mb-3">
                        <h3 class="h6 fw-medium text-dark mb-2">Thời lượng video</h3>
                        <ul class="list-unstyled small text-muted">
                            <li class="form-check mb-1"><input class="form-check-input" type="checkbox" name="video_duration[]" value="0-1" id="duration0-1"><label class="form-check-label" for="duration0-1"> 0-1 Giờ</label></li>
                            <li class="form-check mb-1"><input class="form-check-input" type="checkbox" name="video_duration[]" value="1-3" id="duration1-3"><label class="form-check-label" for="duration1-3"> 1-3 Giờ</label></li>
                            <li class="form-check mb-1"><input class="form-check-input" type="checkbox" name="video_duration[]" value="3-6" id="duration3-6"><label class="form-check-label" for="duration3-6"> 3-6 Giờ</label></li>
                            <li class="form-check mb-1"><input class="form-check-input" type="checkbox" name="video_duration[]" value="6-17" id="duration6-17"><label class="form-check-label" for="duration6-17"> 6-17 Giờ</label></li>
                        </ul>
                    </div>
                    <hr class="my-3">
                     <div class="mb-3">
                        <h3 class="h6 fw-medium text-dark mb-2">Chủ đề</h3>
                        <ul class="list-unstyled small text-muted">
                            <li class="form-check mb-1"><input class="form-check-input" type="checkbox" name="topic[]" value="chatgpt" id="topic_chatgpt"><label class="form-check-label" for="topic_chatgpt"> ChatGPT</label></li>
                            <li class="form-check mb-1"><input class="form-check-input" type="checkbox" name="topic[]" value="prompt_engineering" id="topic_prompt"><label class="form-check-label" for="topic_prompt"> Prompt Engineering</label></li>
                            <li class="form-check mb-1"><input class="form-check-input" type="checkbox" name="topic[]" value="generative_ai" id="topic_gen_ai"><label class="form-check-label" for="topic_gen_ai"> Generative AI</label></li>
                        </ul>
                    </div>
                    <hr class="my-3">
                    <div class="mb-3">
                        <h3 class="h6 fw-medium text-dark mb-2">Danh mục phụ</h3>
                         <ul class="list-unstyled small text-muted">
                            <li class="form-check mb-1"><input class="form-check-input" type="checkbox" name="subcategory[]" value="development" id="sub_dev"><label class="form-check-label" for="sub_dev"> Development</label></li>
                            <li class="form-check mb-1"><input class="form-check-input" type="checkbox" name="subcategory[]" value="business" id="sub_biz"><label class="form-check-label" for="sub_biz"> Business</label></li>
                        </ul>
                    </div>
                    <hr class="my-3">
                    <div class="mb-3">
                        <h3 class="h6 fw-medium text-dark mb-2">Cấp độ</h3>
                        <ul class="list-unstyled small text-muted">
                            <li class="form-check mb-1"><input class="form-check-input" type="checkbox" name="level[]" value="all" id="level_all"><label class="form-check-label" for="level_all"> Tất cả cấp độ</label></li>
                            <li class="form-check mb-1"><input class="form-check-input" type="checkbox" name="level[]" value="beginner" id="level_beginner"><label class="form-check-label" for="level_beginner"> Người mới bắt đầu</label></li>
                            <li class="form-check mb-1"><input class="form-check-input" type="checkbox" name="level[]" value="intermediate" id="level_intermediate"><label class="form-check-label" for="level_intermediate"> Trung bình</label></li>
                            <li class="form-check mb-1"><input class="form-check-input" type="checkbox" name="level[]" value="expert" id="level_expert"><label class="form-check-label" for="level_expert"> Chuyên gia</label></li>
                        </ul>
                    </div>
                     <hr class="my-3">
                    <div class="mb-3">
                        <h3 class="h6 fw-medium text-dark mb-2">Ngôn ngữ</h3>
                        <ul class="list-unstyled small text-muted">
                            <li class="form-check mb-1"><input class="form-check-input" type="checkbox" name="language[]" value="english" id="lang_en"><label class="form-check-label" for="lang_en"> Tiếng Anh</label></li>
                            <li class="form-check mb-1"><input class="form-check-input" type="checkbox" name="language[]" value="vietnamese" id="lang_vi"><label class="form-check-label" for="lang_vi"> Tiếng Việt</label></li>
                        </ul>
                    </div>
                    <hr class="my-3">
                    <div class="mb-3">
                        <h3 class="h6 fw-medium text-dark mb-2">Giá</h3>
                        <ul class="list-unstyled small text-muted">
                            <li class="form-check mb-1"><input class="form-check-input" type="radio" name="price" value="paid" id="price_paid"><label class="form-check-label" for="price_paid"> Trả phí</label></li>
                            <li class="form-check mb-1"><input class="form-check-input" type="radio" name="price" value="free" id="price_free"><label class="form-check-label" for="price_free"> Miễn phí</label></li>
                        </ul>
                    </div>
                    <hr class="my-3">
                    <div class="mb-3">
                        <h3 class="h6 fw-medium text-dark mb-2">Tính năng</h3>
                        <ul class="list-unstyled small text-muted">
                            <li class="form-check mb-1"><input class="form-check-input" type="checkbox" name="features[]" value="exercises" id="feat_exercises"><label class="form-check-label" for="feat_exercises"> Bài tập</label></li>
                            <li class="form-check mb-1"><input class="form-check-input" type="checkbox" name="features[]" value="practice_tests" id="feat_practice"><label class="form-check-label" for="feat_practice"> Bài kiểm tra thực hành</label></li>
                            <li class="form-check mb-1"><input class="form-check-input" type="checkbox" name="features[]" value="subtitles_feature" id="feat_subtitles"><label class="form-check-label" for="feat_subtitles"> Phụ đề</label></li>
                        </ul>
                    </div>
                    <hr class="my-3">
                    <div> {/* No margin bottom for the last item */}
                        <h3 class="h6 fw-medium text-dark mb-2">Phụ đề</h3>
                        <ul class="list-unstyled small text-muted">
                           <li class="form-check mb-1"><input class="form-check-input" type="checkbox" name="subtitles_language[]" value="english" id="sublang_en"><label class="form-check-label" for="sublang_en"> Tiếng Anh</label></li>
                           <li class="form-check mb-1"><input class="form-check-input" type="checkbox" name="subtitles_language[]" value="vietnamese" id="sublang_vi"><label class="form-check-label" for="sublang_vi"> Tiếng Việt</label></li>
                        </ul>
                    </div>
                </div>
            </aside>

            <div class="col-md-8 col-lg-9">
                <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center mb-3">
                    <div class="d-flex align-items-center mb-2 mb-sm-0">
                        <button id="filterToggleButton" class="btn btn-custom-purple d-md-none me-3">
                            <i class="fas fa-filter me-1"></i> Lọc
                        </button>
                        <span class="text-muted"><span id="courseCount">1,716</span> kết quả</span>
                    </div>
                    <div class="mt-2 mt-sm-0">
                        <label for="sortBy" class="visually-hidden">Sắp xếp theo</label>
                        <select id="sortBy" name="sortBy" class="form-select form-select-sm" style="min-width: 180px;">
                            <option value="highest_rated">Đánh giá cao nhất</option>
                            <option value="newest">Mới nhất</option>
                            <option value="most_popular">Phổ biến nhất</option>
                        </select>
                    </div>
                </div>

                <div id="courseList">
                    <div class="course-card card mb-3 shadow-sm">
                        <div class="row g-0">
                            <div class="col-lg-4">
                                <img src="https://placehold.co/240x135/E2E8F0/94A3B8?text=Ảnh+khóa+học" alt="Hình ảnh khóa học" class="img-fluid rounded-start w-100 h-100" style="object-fit: cover; max-height: 150px;" onerror="this.onerror=null;this.src='https://placehold.co/240x135/E2E8F0/94A3B8?text=Lỗi+ảnh';">
                            </div>
                            <div class="col-lg-8">
                                <div class="card-body d-flex flex-column flex-md-row p-3">
                                    <div class="flex-grow-1 mb-3 mb-md-0 me-md-3">
                                        <h5 class="card-title fw-semibold text-dark course-title-link">Free Website Traffic Guide for Affiliate Marketing & ChatGPT</h5>
                                        <p class="card-text small text-muted mb-1">Turn Clicks into Cash: Master Affiliate Marketing with ChatGPT and Zero Ad Spend on Website Traffic</p>
                                        <p class="card-text text-muted mb-1" style="font-size: 0.75rem;">Being Commerce</p>
                                        <div class="d-flex align-items-center mb-1 star-rating">
                                            <span class="small fw-bold me-1 rating-value">5.0</span>
                                            <span class="text-warning rating-stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></span>
                                            <span class="ms-1 rating-count" style="font-size: 0.75rem; color: #6c757d;">(1,234)</span>
                                        </div>
                                        <p class="card-text small text-muted">Tổng 5 giờ · 50 bài giảng · Tất cả cấp độ</p>
                                    </div>
                                    <div class="text-md-end price-section flex-shrink-0 align-self-md-center">
                                        <p class="h5 fw-bold text-custom-purple mb-0">₫199,000</p>
                                        <p class="small text-muted text-decoration-line-through">₫399,000</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="course-card card mb-3 shadow-sm">
                        <div class="row g-0">
                            <div class="col-lg-4">
                                <img src="https://placehold.co/240x135/D1FAE5/34D399?text=Khóa+học+AI" alt="Hình ảnh khóa học" class="img-fluid rounded-start w-100 h-100" style="object-fit: cover; max-height: 150px;" onerror="this.onerror=null;this.src='https://placehold.co/240x135/E2E8F0/94A3B8?text=Lỗi+ảnh';">
                            </div>
                            <div class="col-lg-8">
                                <div class="card-body d-flex flex-column flex-md-row p-3">
                                    <div class="flex-grow-1 mb-3 mb-md-0 me-md-3">
                                        <h5 class="card-title fw-semibold text-dark course-title-link">ChatGPT Mastery: 10 Prompt Patterns, 8 Skills + RealLife uses</h5>
                                        <p class="card-text small text-muted mb-1">ChatGPT, Prompt Engineering, Productivity, Generative AI, ChatGPT Real-life Applications, ChatGPT Automation</p>
                                        <p class="card-text text-muted mb-1" style="font-size: 0.75rem;">Musawir Hassan</p>
                                        <div class="d-flex align-items-center mb-1 star-rating">
                                            <span class="small fw-bold me-1 rating-value">5.0</span>
                                            <span class="text-warning rating-stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></span>
                                            <span class="ms-1 rating-count" style="font-size: 0.75rem; color: #6c757d;">(2,345)</span>
                                        </div>
                                        <p class="card-text small text-muted">Tổng 3.5 giờ · 42 bài giảng · Người mới bắt đầu</p>
                                    </div>
                                    <div class="text-md-end price-section flex-shrink-0 align-self-md-center">
                                        <p class="h5 fw-bold text-custom-purple mb-0">₫199,000</p>
                                        <p class="small text-muted text-decoration-line-through">₫399,000</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="course-card card mb-3 shadow-sm">
                        <div class="row g-0">
                            <div class="col-lg-4">
                                <img src="https://placehold.co/240x135/C7D2FE/6366F1?text=App+Dev" alt="Hình ảnh khóa học" class="img-fluid rounded-start w-100 h-100" style="object-fit: cover; max-height: 150px;" onerror="this.onerror=null;this.src='https://placehold.co/240x135/E2E8F0/94A3B8?text=Lỗi+ảnh';">
                            </div>
                            <div class="col-lg-8">
                                <div class="card-body d-flex flex-column flex-md-row p-3">
                                    <div class="flex-grow-1 mb-3 mb-md-0 me-md-3">
                                        <h5 class="card-title fw-semibold text-dark course-title-link">KivyMD App with ChatGPT and Convert to APK & AAB File</h5>
                                        <p class="card-text small text-muted mb-1">AI-Enhanced APK & AAB: Develop Python Kivy Apps with ChatGPT and Crash-Free Conversion</p>
                                        <p class="card-text text-muted mb-1" style="font-size: 0.75rem;">Alduruman TEKIN</p>
                                        <div class="d-flex align-items-center mb-1 star-rating">
                                            <span class="small fw-bold me-1 rating-value">4.8</span>
                                            <span class="text-warning rating-stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i></span>
                                            <span class="ms-1 rating-count" style="font-size: 0.75rem; color: #6c757d;">(987)</span>
                                        </div>
                                        <p class="card-text small text-muted">Tổng 10 giờ · 120 bài giảng · Trung bình</p>
                                    </div>
                                    <div class="text-md-end price-section flex-shrink-0 align-self-md-center">
                                        <p class="h5 fw-bold text-custom-purple mb-0">₫199,000</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <nav class="mt-4 d-flex justify-content-center" aria-label="Pagination">
                    <ul class="pagination">
                        <li class="page-item">
                            <a class="page-link" href="#" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                                <span class="visually-hidden">Trước</span>
                            </a>
                        </li>
                        <li class="page-item active" aria-current="page"><a class="page-link" href="#">1</a></li>
                        <li class="page-item"><a class="page-link" href="#">2</a></li>
                        <li class="page-item"><a class="page-link" href="#">3</a></li>
                        <li class="page-item disabled"><span class="page-link">...</span></li>
                        <li class="page-item"><a class="page-link" href="#">10</a></li>
                        <li class="page-item">
                            <a class="page-link" href="#" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                                <span class="visually-hidden">Sau</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </section>
    </main>

    <?php include('template/footer.php'); // Include file footer.php ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // JavaScript cho các tương tác cơ bản
        const filterToggleButton = document.getElementById('filterToggleButton');
        const sidebar = document.getElementById('filterSidebar');

        if (filterToggleButton && sidebar) {
            filterToggleButton.addEventListener('click', () => {
                // Toggle class 'd-none' của Bootstrap để ẩn/hiện sidebar trên màn hình nhỏ
                sidebar.classList.toggle('d-none');
            });
        }

        // Xử lý bộ lọc và sắp xếp (ví dụ cơ bản)
        const filterInputs = document.querySelectorAll('#filterSidebar input[type="checkbox"], #filterSidebar input[type="radio"]');
        const sortBySelect = document.getElementById('sortBy');
        const courseListContainer = document.getElementById('courseList');
        const courseCountElement = document.getElementById('courseCount');

        function applyFiltersAndSort() {
            const selectedFilters = {};
            filterInputs.forEach(input => {
                if (input.type === 'checkbox' && input.checked) {
                    if (!selectedFilters[input.name]) {
                        selectedFilters[input.name] = [];
                    }
                    selectedFilters[input.name].push(input.value);
                } else if (input.type === 'radio' && input.checked) {
                    selectedFilters[input.name] = input.value;
                }
            });

            const sortBy = sortBySelect ? sortBySelect.value : 'highest_rated';

            console.log("Đang áp dụng bộ lọc:", selectedFilters);
            console.log("Sắp xếp theo:", sortBy);

            // Placeholder for AJAX call to PHP script
            // fetch('your_php_filter_script.php', { /* ... */ })
            // .then(response => response.json())
            // .then(data => {
            //     updateCourseList(data.courses);
            //     updateCourseCount(data.courseCount);
            // })
            // .catch(error => console.error('Lỗi khi lọc khóa học:', error));
            
            // Mock update for demonstration
            // const mockCourses = []; // Add mock data here to test updateCourseList
            // updateCourseList(mockCourses);
            // updateCourseCount(mockCourses.length);
        }

        filterInputs.forEach(input => {
            input.addEventListener('change', applyFiltersAndSort);
        });
        if (sortBySelect) {
            sortBySelect.addEventListener('change', applyFiltersAndSort);
        }

        function updateCourseList(coursesData) {
            if (!courseListContainer) return;
            courseListContainer.innerHTML = ''; 
            if (coursesData && coursesData.length > 0) {
                coursesData.forEach(course => {
                    const courseElement = document.createElement('div');
                    courseElement.className = 'course-card card mb-3 shadow-sm';
                    const imageUrl = course.imageUrl || `https://placehold.co/240x135/E2E8F0/94A3B8?text=${encodeURIComponent(course.title || 'Khóa học')}`;
                    const priceHTML = course.price && Number(course.price) > 0 ? `₫${Number(course.price).toLocaleString('vi-VN')}` : 'Miễn phí';
                    const originalPriceHTML = course.original_price && Number(course.original_price) > 0 ? `<p class="small text-muted text-decoration-line-through">₫${Number(course.original_price).toLocaleString('vi-VN')}</p>` : '';

                    courseElement.innerHTML = `
                        <div class="row g-0">
                            <div class="col-lg-4">
                                <img src="${imageUrl}" alt="Hình ảnh khóa học ${course.title || ''}" class="img-fluid rounded-start w-100 h-100" style="object-fit: cover; max-height: 150px;" onerror="this.onerror=null;this.src='https://placehold.co/240x135/E2E8F0/94A3B8?text=Lỗi+ảnh';">
                            </div>
                            <div class="col-lg-8">
                                <div class="card-body d-flex flex-column flex-md-row p-3">
                                    <div class="flex-grow-1 mb-3 mb-md-0 me-md-3">
                                        <h5 class="card-title fw-semibold text-dark course-title-link">${course.title || 'Không có tiêu đề'}</h5>
                                        <p class="card-text small text-muted mb-1">${course.description_short || ''}</p>
                                        <p class="card-text text-muted mb-1" style="font-size: 0.75rem;">${course.instructor || 'Chưa có thông tin giảng viên'}</p>
                                        <div class="d-flex align-items-center mb-1 star-rating">
                                            <span class="small fw-bold me-1 rating-value">${course.rating_value || 'N/A'}</span>
                                            <span class="text-warning rating-stars">${generateStars(course.rating_value)}</span>
                                            <span class="ms-1 rating-count" style="font-size: 0.75rem; color: #6c757d;">(${course.rating_count || 0})</span>
                                        </div>
                                        <p class="card-text small text-muted">${course.duration || 'N/A'} · ${course.lecture_count || 'N/A'} bài giảng · ${course.level || 'N/A'}</p>
                                    </div>
                                    <div class="text-md-end price-section flex-shrink-0 align-self-md-center">
                                        <p class="h5 fw-bold text-custom-purple mb-0">${priceHTML}</p>
                                        ${originalPriceHTML}
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    courseListContainer.appendChild(courseElement);
                });
            } else {
                courseListContainer.innerHTML = '<p class="text-muted text-center py-5">Không tìm thấy khóa học nào phù hợp với tiêu chí của bạn.</p>';
            }
        }

        function generateStars(rating) {
            let starsHTML = '';
            const numRating = parseFloat(rating);
            if (isNaN(numRating) || numRating < 0 || numRating > 5) {
                for (let i = 0; i < 5; i++) starsHTML += '<i class="far fa-star text-muted"></i>';
                return starsHTML;
            }
            const fullStars = Math.floor(numRating);
            const halfStar = (numRating % 1) >= 0.25 && (numRating % 1) < 0.75;
            const almostFullStar = (numRating % 1) >= 0.75;
            let currentFullStars = fullStars;
            if (almostFullStar) currentFullStars++;
            for (let i = 0; i < currentFullStars; i++) starsHTML += '<i class="fas fa-star"></i>';
            if (halfStar && !almostFullStar) starsHTML += '<i class="fas fa-star-half-alt"></i>';
            const emptyStars = 5 - currentFullStars - (halfStar && !almostFullStar ? 1 : 0);
            for (let i = 0; i < emptyStars; i++) starsHTML += '<i class="far fa-star"></i>';
            return starsHTML;
        }

        function updateCourseCount(count) {
            if (courseCountElement) {
                courseCountElement.textContent = Number(count).toLocaleString('vi-VN');
            }
        }
        // Initial load example (if not server-rendered)
        // const initialCourseCards = document.querySelectorAll('#courseList .course-card');
        // updateCourseCount(initialCourseCards.length);
        // applyFiltersAndSort(); // Call if you want to load data via JS on page load
    </script>
</body>
</html>
