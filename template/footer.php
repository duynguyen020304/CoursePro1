
<footer class="site-footer bg-dark text-white mt-5 pt-5 pb-4">
  <div class="container">
    <div class="row gy-4"> <div class="col-lg-4 col-md-12 mb-4 mb-lg-0 footer-col"> <h5 class="text-uppercase mb-4 footer-heading">Course Online</h5>
        <p class="small footer-description-text">
          Nền tảng học tập trực tuyến hiện đại, cung cấp các khóa học đa dạng và chất lượng, giúp bạn phát triển kỹ năng và kiến thức mọi lúc, mọi nơi.
        </p>
        <div class="mt-4 footer-contact-info">
          <a href="mailto:support@courseonline.vn" class="text-white me-3 footer-contact-link">
            <i class="fas fa-envelope me-2"></i>support@courseonline.vn
          </a>
          <br class="d-md-none"> <a href="tel:0123456789" class="text-white footer-contact-link">
            <i class="fas fa-phone me-2"></i>0123 456 789
          </a>
        </div>
      </div>

      <div class="col-lg-2 col-md-4 col-6 footer-col">
        <h5 class="text-uppercase mb-4 footer-heading">Liên kết</h5>
        <ul class="list-unstyled footer-links">
          <li><a href="courses.php"><i class="fas fa-book-open me-2"></i>Khóa học</a></li>
          <li><a href="about.php"><i class="fas fa-info-circle me-2"></i>Giới thiệu</a></li>
          <li><a href="contact.php"><i class="fas fa-headset me-2"></i>Liên hệ</a></li>
          <li><a href="faq.php"><i class="fas fa-question-circle me-2"></i>FAQ</a></li>
        </ul>
      </div>

      <div class="col-lg-3 col-md-4 col-6 footer-col">
        <h5 class="text-uppercase mb-4 footer-heading">Hỗ trợ</h5>
        <ul class="list-unstyled footer-links">
          <li><a href="terms.php"><i class="fas fa-file-contract me-2"></i>Điều khoản</a></li>
          <li><a href="privacy.php"><i class="fas fa-shield-alt me-2"></i>Bảo mật</a></li>
          <li><a href="sitemap.php"><i class="fas fa-sitemap me-2"></i>Sơ đồ trang</a></li>
          <li><a href="blog.php"><i class="fas fa-blog me-2"></i>Blog</a></li> </ul>
      </div>

      <div class="col-lg-3 col-md-4 footer-col">
        <h5 class="text-uppercase mb-4 footer-heading">Theo dõi</h5>
        <div class="social-icons mb-3">
          <a href="https://facebook.com/yourpage" target="_blank" title="Facebook" class="social-icon me-2"><i class="fab fa-facebook-f"></i></a>
          <a href="https://instagram.com/yourprofile" target="_blank" title="Instagram" class="social-icon me-2"><i class="fab fa-instagram"></i></a>
          <a href="https://youtube.com/yourchannel" target="_blank" title="Youtube" class="social-icon me-2"><i class="fab fa-youtube"></i></a>
          <a href="https://linkedin.com/yourcompany" target="_blank" title="LinkedIn" class="social-icon"><i class="fab fa-linkedin-in"></i></a>
        </div>
        <p class="small footer-address-text">
          <i class="fas fa-map-marker-alt me-2"></i>123 Đường ABC, Phường X, Quận Y, TP. Z
        </p>
      </div>
    </div>

    <hr class="my-4 footer-divider">

    <div class="row footer-bottom-row">
      <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
        <p class="small mb-0 footer-copyright-text">&copy; <span id="currentYear"></span> CourseOnline. All rights reserved.</p>
      </div>
      <div class="col-md-6 text-center text-md-end">
        <p class="small mb-0 footer-credits-text">Thiết kế bởi <a href="https://yourweborcompany.com" target="_blank" class="text-white fw-bold">Tên Bạn/Công Ty</a></p>
      </div>
    </div>
  </div>
</footer>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const currentYearElement = document.getElementById('currentYear');
    if (currentYearElement) {
      currentYearElement.textContent = new Date().getFullYear();
    }
  });
</script>
