// public/js/watch_video.js
document.addEventListener('DOMContentLoaded', function () {
    const courseSidebar = document.querySelector('.course-content-sidebar');
    const openSidebarBtnMobile = document.getElementById('openCourseContentSidebarMobile');
    const closeSidebarBtnMobile = document.getElementById('closeCourseContentSidebarMobile');
    let sidebarOverlay = document.querySelector('.sidebar-overlay');

    // Tạo overlay nếu chưa có
    if (!sidebarOverlay) {
        sidebarOverlay = document.createElement('div');
        sidebarOverlay.classList.add('sidebar-overlay');
        document.body.appendChild(sidebarOverlay);
    }

    function openSidebar() {
        if (courseSidebar) courseSidebar.classList.add('open');
        if (sidebarOverlay) sidebarOverlay.classList.add('active');
        document.body.style.overflow = 'hidden'; // Ngăn cuộn body khi sidebar mở
    }

    function closeSidebar() {
        if (courseSidebar) courseSidebar.classList.remove('open');
        if (sidebarOverlay) sidebarOverlay.classList.remove('active');
        document.body.style.overflow = ''; // Cho phép cuộn lại
    }

    if (openSidebarBtnMobile) {
        openSidebarBtnMobile.addEventListener('click', function (event) {
            event.stopPropagation(); // Ngăn sự kiện click lan ra overlay
            openSidebar();
        });
    }

    if (closeSidebarBtnMobile) {
        closeSidebarBtnMobile.addEventListener('click', function () {
            closeSidebar();
        });
    }

    // Đóng sidebar khi click vào overlay
    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', function () {
            closeSidebar();
        });
    }

    // Đóng sidebar khi nhấn phím Escape
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && courseSidebar && courseSidebar.classList.contains('open')) {
            closeSidebar();
        }
    });


    // (Tùy chọn) Tự động đóng sidebar khi click vào một bài học trên mobile
    const lessonLinks = courseSidebar ? courseSidebar.querySelectorAll('.lesson-list a') : [];
    lessonLinks.forEach(link => {
        link.addEventListener('click', function() {
            // Bỏ active class khỏi tất cả các link khác
            lessonLinks.forEach(l => l.classList.remove('active-lesson'));
            // Thêm active class cho link được click
            this.classList.add('active-lesson');

            // Thay đổi icon
            document.querySelectorAll('.lesson-list a i').forEach(icon => {
                icon.classList.remove('bi-play-circle-fill');
                icon.classList.add('bi-play-circle');
            });
            this.querySelector('i').classList.remove('bi-play-circle');
            this.querySelector('i').classList.add('bi-play-circle-fill');


            if (window.innerWidth < 992 && courseSidebar && courseSidebar.classList.contains('open')) {
                // closeSidebar(); // Tạm thời comment dòng này nếu bạn muốn người dùng tự đóng
            }
            // TODO: Logic để tải video mới hoặc cuộn đến video tương ứng
            console.log('Clicked lesson:', this.textContent.trim());
        });
    });

    // Xử lý các tab để lưu trạng thái active (ví dụ dùng localStorage) nếu cần
    const tabTriggers = document.querySelectorAll('#videoTab button[data-bs-toggle="tab"]');
    tabTriggers.forEach(tabTrigger => {
        tabTrigger.addEventListener('shown.bs.tab', event => {
            // console.log('Active tab:', event.target.id);
            // localStorage.setItem('activeVideoInfoTab', event.target.id);
        });
    });

    // const lastActiveTab = localStorage.getItem('activeVideoInfoTab');
    // if (lastActiveTab) {
    //     const tabElement = document.getElementById(lastActiveTab);
    //     if (tabElement) {
    //         new bootstrap.Tab(tabElement).show();
    //     }
    // }

});