<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$token = $_SESSION['user']['token'] ?? null;
include(__DIR__ . '/../templates/head.php');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Khóa học theo Danh mục</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/font_awesome_all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="../assets/css/course-category.css" rel="stylesheet">
</head>
<body class="bg-light">

<?php include(__DIR__ . '/../templates/header.php'); ?>

<main class="container py-4 py-md-5">

    <section class="row g-4">
        <div class="col-12">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center mb-3">
                <div class="d-flex align-items-center mb-2 mb-sm-0">
                    <span class="text-muted"><span id="courseCount">0</span> kết quả</span>
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
                <p class="text-center py-5">Đang tải khóa học...</p>
            </div>

            <nav id="paginationContainer" class="mt-4 d-flex justify-content-center" aria-label="Pagination">
                <ul class="pagination">
                    <li class="page-item disabled">
                        <a class="page-link" href="#" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                            <span class="visually-hidden">Trước</span>
                        </a>
                    </li>
                    <li class="page-item active" aria-current="page"><a class="page-link" href="#">1</a></li>
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

<?php include(__DIR__ . '/../templates/footer.php'); ?>

<script src="../assets/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const sortBySelect = document.getElementById('sortBy');
        const courseListContainer = document.getElementById('courseList');
        const courseCountElement = document.getElementById('courseCount');
        const categoryTitleElement = document.getElementById('categoryTitle');
        const categoryDescriptionElement = document.getElementById('categoryDescription');
        const learnerCountElement = document.getElementById('learnerCount');
        const categoryCourseCountElement = document.getElementById('categoryCourseCount');
        const labCountElement = document.getElementById('labCount');
        const avgRatingCategoryElement = document.getElementById('avgRatingCategory');

        const phpToken = <?php echo json_encode($token); ?>;
        let allFetchedCourses = [];

        function getCategoryIdFromUrl() {
            const urlParams = new URLSearchParams(window.location.search);
            return urlParams.get('categoryID');
        }

        async function fetchCourseDetails(courseId) {
            if (!courseId) {
                console.warn("fetchCourseDetails called with invalid courseId:", courseId);
                return null;
            }
            try {
                const courseApiUrl = `http://localhost/CoursePro1/api/course_api.php?courseID=${courseId}&isFilterByCategory=true`;
                const response = await fetch(courseApiUrl, {
                    method: 'GET',
                    headers: {
                        'Authorization': `Bearer ${phpToken}`,
                        'Content-Type': 'application/json'
                    }
                });
                if (!response.ok) {
                    console.error(`Lỗi HTTP khi lấy chi tiết khóa học ${courseId}: ${response.status}`);
                    const errorData = await response.json().catch(() => ({ message: 'Không thể phân tích JSON lỗi' }));
                    console.error('Chi tiết lỗi:', errorData);
                    return null;
                }
                const result = await response.json();
                if (result.success && result.data) {
                    return result.data;
                } else {
                    console.error(`Lỗi API khi lấy chi tiết khóa học ${courseId}: ${result.message}`);
                    return null;
                }
            } catch (error) {
                console.error(`Lỗi khi lấy chi tiết cho khóa học ${courseId}:`, error);
                return null;
            }
        }

        async function fetchCoursesByCategory(categoryId) {
            if (!courseListContainer) return;
            courseListContainer.innerHTML = '<p class="text-center py-5">Đang tải khóa học...</p>';

            if (!phpToken) {
                courseListContainer.innerHTML = '<p class="text-danger text-center py-5">Lỗi: Không tìm thấy token xác thực. Vui lòng đăng nhập lại.</p>';
                console.error("PHP token is missing.");
                updateCourseCount(0);
                return;
            }

            try {
                const categoryApiUrl = `http://localhost/CoursePro1/api/course_category_api.php?categoryID=${categoryId}`;
                const categoryResponse = await fetch(categoryApiUrl, {
                    method: 'GET',
                    headers: {
                        'Authorization': `Bearer ${phpToken}`,
                        'Content-Type': 'application/json'
                    }
                });

                if (!categoryResponse.ok) {
                    const errorText = await categoryResponse.text();
                    throw new Error(`Lỗi khi lấy danh sách khóa học theo danh mục: ${categoryResponse.status} ${errorText}`);
                }

                const categoryResult = await categoryResponse.json();

                if (categoryResult.categoryName && categoryTitleElement) {
                    categoryTitleElement.textContent = categoryResult.categoryName;
                } else if (categoryTitleElement) {
                    categoryTitleElement.textContent = `Khóa học trong Danh mục ${categoryId}`;
                }
                if (categoryResult.categoryDescription && categoryDescriptionElement) {
                    categoryDescriptionElement.textContent = categoryResult.categoryDescription;
                }

                if (categoryResult.stats) {
                    if (learnerCountElement) learnerCountElement.textContent = categoryResult.stats.totalLearners || '-';
                    if (categoryCourseCountElement) categoryCourseCountElement.textContent = categoryResult.stats.totalCourses || '-';
                    if (labCountElement) labCountElement.innerHTML = `${categoryResult.stats.totalLabs || '-'} <i class="fas fa-info-circle small text-muted"></i>`;
                    if (avgRatingCategoryElement) avgRatingCategoryElement.innerHTML = `${parseFloat(categoryResult.stats.averageRating || 0).toFixed(1)} <i class="fas fa-star text-warning"></i>`;
                }

                if (!categoryResult.success || !Array.isArray(categoryResult.data)) {
                    console.error('Lỗi API hoặc định dạng dữ liệu không hợp lệ từ course_category_api:', categoryResult.message, categoryResult.data);
                    courseListContainer.innerHTML = `<p class="text-muted text-center py-5">Không tìm thấy khóa học nào cho danh mục này hoặc có lỗi xảy ra. ${categoryResult.message || ''}</p>`;
                    updateCourseCount(0);
                    return;
                }

                const courseInfos = categoryResult.data;

                if (courseInfos.length === 0) {
                    courseListContainer.innerHTML = '<p class="text-muted text-center py-5">Không có khóa học nào trong danh mục này.</p>';
                    updateCourseCount(0);
                    if (categoryCourseCountElement) categoryCourseCountElement.textContent = '0';
                    return;
                }
                if (categoryCourseCountElement) categoryCourseCountElement.textContent = courseInfos.length;


                const courseDetailPromises = courseInfos.map(info => {
                    if (!info || typeof info.courseID === 'undefined' || info.courseID === null) {
                        console.warn("Đối tượng thông tin khóa học không có courseID hoặc courseID không hợp lệ:", info);
                        return Promise.resolve(null);
                    }
                    return fetchCourseDetails(info.courseID);
                });

                const detailedCoursesResults = await Promise.all(courseDetailPromises);
                allFetchedCourses = detailedCoursesResults.filter(course => course !== null && typeof course === 'object');

                updateCourseList(allFetchedCourses);
                updateCourseCount(allFetchedCourses.length);

            } catch (error) {
                console.error('Lỗi khi tải khóa học:', error);
                courseListContainer.innerHTML = `<p class="text-danger text-center py-5">Đã xảy ra lỗi khi tải khóa học. Vui lòng thử lại. Chi tiết: ${error.message}</p>`;
                updateCourseCount(0);
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

            for (let i = 0; i < fullStars; i++) {
                starsHTML += '<i class="fas fa-star"></i>';
            }
            if (halfStar) {
                starsHTML += '<i class="fas fa-star-half-alt"></i>';
            }
            const emptyStars = 5 - fullStars - (halfStar ? 1 : 0);
            for (let i = 0; i < emptyStars; i++) {
                starsHTML += '<i class="far fa-star"></i>';
            }
            return starsHTML;
        }

        function updateCourseList(coursesData) {
            if (!courseListContainer) return;
            courseListContainer.innerHTML = '';

            if (coursesData && coursesData.length > 0) {
                coursesData.forEach(course => {
                    if (!course || typeof course.courseID === 'undefined') {
                        console.warn("Dữ liệu khóa học không hợp lệ hoặc thiếu courseID:", course);
                        return;
                    }

                    const courseElement = document.createElement('div');
                    courseElement.className = 'course-card card mb-3 shadow-sm';

                    const title = course.title || 'Không có tiêu đề';

                    let imageUrl;
                    if (course.images && Array.isArray(course.images) && course.images.length > 0) {
                        const imageFilename = course.images[0]['imagePath'];
                        if (typeof imageFilename === 'string' && imageFilename.trim() !== '') {
                            imageUrl = `http://localhost/CoursePr../../backend/Controller/Form/c_file_loader.php?act=serve_image&course_id=${course.courseID}&image=${encodeURIComponent(imageFilename)}`;
                        }
                    }
                    if (!imageUrl && course.image_url) {
                        if (course.image_url.startsWith('http://') || course.image_url.startsWith('https://')) {
                            imageUrl = course.image_url;
                        } else {
                            imageUrl = `http://localhost/CoursePr../../backend/Controller/Form/c_file_loader.php?act=serve_image&course_id=${course.courseID}&image=${encodeURIComponent(course.image_url)}`;
                        }
                    }
                    if (!imageUrl) {
                        imageUrl = `https://placehold.co/240x135/E2E8F0/94A3B8?text=${encodeURIComponent(title)}`;
                    }


                    const shortDescription = course.short_description || course.description || 'Không có mô tả ngắn.';
                    const instructorNames = course.instructor_names_concat || (course.instructors && course.instructors.length > 0 ? course.instructors.map(i => `${i.firstName} ${i.lastName}`).join(', ') : 'Nhiều giảng viên');
                    const avgRating = parseFloat(course.avg_rating || 0).toFixed(1);
                    const totalRatingsCount = parseInt(course.total_ratings_count || 0).toLocaleString('vi-VN');

                    const duration = course.total_duration_text || (course.total_duration ? `${Math.round(course.total_duration / 3600)} giờ` : 'N/A');
                    const lectureCount = course.total_lectures_count || course.totalLesson || 'N/A';
                    const level = course.level_name || 'Mọi cấp độ';


                    const priceValue = parseFloat(course.price);
                    const originalPriceValue = course.original_price ? parseFloat(course.original_price) : 0;

                    const priceHTML = !isNaN(priceValue) && priceValue > 0 ? `₫${priceValue.toLocaleString('vi-VN')}` : 'Miễn phí';
                    const originalPriceHTML = !isNaN(originalPriceValue) && originalPriceValue > priceValue ? `<p class="small text-muted text-decoration-line-through mb-0">₫${originalPriceValue.toLocaleString('vi-VN')}</p>` : '';

                    const courseDetailUrl = `course-detail.php?courseID=${course.courseID}`;

                    courseElement.innerHTML = `
                    <div class="row g-0">
                        <div class="col-lg-3 col-md-4">
                            <img src="${imageUrl}" alt="Hình ảnh khóa học ${title}" class="img-fluid rounded-start w-100 h-100" style="object-fit: cover; max-height: 180px; min-height:150px;" onerror="this.onerror=null;this.src='https://placehold.co/240x135/E2E8F0/94A3B8?text=Lỗi+ảnh';">
                        </div>
                        <div class="col-lg-9 col-md-8">
                            <div class="card-body d-flex flex-column flex-sm-row p-3">
                                <div class="flex-grow-1 mb-3 mb-md-0 me-md-3">
                                    <h5 class="card-title fw-semibold text-dark mb-1">
                                        <a href="${courseDetailUrl}" class="text-decoration-none text-dark course-title-link">${title}</a>
                                    </h5>
                                    <p class="card-text small text-muted mb-1 d-none d-sm-block">${shortDescription.substring(0,100)}${shortDescription.length > 100 ? '...' : ''}</p>
                                    <p class="card-text text-muted mb-1" style="font-size: 0.8rem;">${instructorNames}</p>
                                    <div class="d-flex align-items-center mb-2 star-rating flex-wrap">
                                        <span class="small fw-bold me-1 rating-value text-warning">${avgRating}</span>
                                        <span class="text-warning rating-stars me-1">${generateStars(avgRating)}</span>
                                        <span class="ms-1 rating-count" style="font-size: 0.75rem; color: #6c757d;">(${totalRatingsCount} đánh giá)</span>
                                    </div>
                                    <p class="card-text small text-muted" style="font-size: 0.8rem;">${lectureCount} bài giảng · ${level}</p>
                                </div>
                                <div class="text-md-end price-section flex-shrink-0 align-self-md-start mt-2 mt-md-0">
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

        function updateCourseCount(count) {
            if (courseCountElement) {
                courseCountElement.textContent = Number(count).toLocaleString('vi-VN');
            }
        }

        function applySort() {
            const sortBy = sortBySelect ? sortBySelect.value : 'highest_rated';
            console.log("Sắp xếp theo:", sortBy);

            if (allFetchedCourses && allFetchedCourses.length > 0) {
                let sortedCourses = [...allFetchedCourses];

                switch (sortBy) {
                    case 'highest_rated':
                        sortedCourses.sort((a, b) => (parseFloat(b.avg_rating) || 0) - (parseFloat(a.avg_rating) || 0));
                        break;
                    case 'newest':
                        if (sortedCourses[0] && (sortedCourses[0].created_at || sortedCourses[0].createdAt)) {
                            sortedCourses.sort((a, b) => {
                                const dateA = new Date(a.created_at || a.createdAt);
                                const dateB = new Date(b.created_at || b.createdAt);
                                return dateB - dateA;
                            });
                        } else {
                            console.warn("Không thể sắp xếp theo 'Mới nhất': thiếu trường dữ liệu ngày tháng (ví dụ: created_at).");
                        }
                        break;
                    case 'most_popular':
                        sortedCourses.sort((a, b) => (parseInt(b.total_ratings_count) || 0) - (parseInt(a.total_ratings_count) || 0));
                        break;
                }
                updateCourseList(sortedCourses);
            } else {
                console.log("Không có khóa học để sắp xếp.");
            }
        }

        if (sortBySelect) {
            sortBySelect.addEventListener('change', applySort);
        }

        const categoryId = getCategoryIdFromUrl();
        if (categoryId) {
            fetchCoursesByCategory(categoryId);
        } else {
            if (courseListContainer) {
                courseListContainer.innerHTML = '<p class="text-warning text-center py-5">Vui lòng cung cấp một ID danh mục trong URL (ví dụ: ?categoryID=1) để xem các khóa học.</p>';
            }
            updateCourseCount(0);
            if (categoryTitleElement) categoryTitleElement.textContent = 'Không có danh mục nào được chọn';
            console.warn("CategoryID không tìm thấy trong URL.");
        }
    });
</script>
</body>
</html>
