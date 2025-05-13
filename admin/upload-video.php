<?php
session_start();
// Dữ liệu giả lập cho dropdown Khóa học (sẽ được thay thế bằng PHP lấy từ DB sau)
$courses_placeholder = [
    ['id' => '1', 'name' => 'Khóa học Web Design Cơ Bản'],
    ['id' => '2', 'name' => 'Khóa học PHP Nâng Cao'],
    ['id' => '3', 'name' => 'Khóa học Lập trình Python cho Người Mới Bắt Đầu']
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Thêm Nội dung Khóa học - Trang Quản Trị</title>
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <link rel="stylesheet" href="css/font_awesome_all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <link rel="stylesheet" href="css/admin_style.css">
  <link rel="stylesheet" href="css/base_dashboard.css">
  <style>
    .container-fluid { padding-top: 20px; }
    .form-container { background:#fff; padding:30px; border-radius:8px; box-shadow:0 0 15px rgba(0,0,0,0.1); }
    .section-title { margin:20px 0 15px; font-size:1.25rem; color:#333; border-bottom:1px solid #eee; padding-bottom:10px; }
    .topic-item .card-header { cursor: move; }
  </style>
</head>
<body>
  <div class="dashboard-container">
    <?php if (file_exists('template/dashboard.php')) include 'template/dashboard.php'; ?>
    <div class="main-content">
      <div class="container-fluid">
        <div class="form-container">
          <h2>Thêm Nội dung Khóa học (Chương & Bài học Video)</h2>
          <hr>

          <form action="#" method="post" enctype="multipart/form-data" id="addContentForm">
            <!-- 1. Chọn Khóa học -->
            <div class="mb-3">
              <label for="course_id" class="form-label"><strong>1. Chọn Khóa học:</strong> <span class="text-danger">*</span></label>
              <select id="course_id" name="course_id" class="form-select" required>
                <option value="">-- Chọn Khóa học --</option>
                <?php foreach($courses_placeholder as $c): ?>
                  <option value="<?=htmlspecialchars($c['id'])?>"><?=htmlspecialchars($c['name'])?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <!-- 2. Course Builder UI -->
            <h4 class="section-title"><i class="bi bi-folder-plus"></i> 2. Xây dựng nội dung khóa học</h4>
            <!-- Nút thêm chương mới -->
            <button id="btn-add-topic" type="button" class="btn btn-primary mb-3">
              <i class="bi bi-plus-lg"></i> Add New Topic
            </button>
            <!-- Form thêm topic (ẩn) -->
            <div id="add-topic-form" class="card p-3 mb-4 collapse">
              <div class="mb-3">
                <label for="topic-name" class="form-label">Tên Chương</label>
                <input type="text" id="topic-name" class="form-control" placeholder="Ví dụ: Chương 1 - Giới thiệu" required>
              </div>
              <div class="mb-3">
                <label for="topic-summary" class="form-label">Mô tả Chương (tùy chọn)</label>
                <textarea id="topic-summary" class="form-control" rows="2" placeholder="Mô tả ngắn..."></textarea>
              </div>
              <button id="save-topic" type="button" class="btn btn-success">Add Topic</button>
            </div>
            <!-- List topic sẽ được chèn vào đây -->
            <div id="topics-list"></div>

            <!-- Nút Lưu toàn bộ -->
            <div class="mt-4">
              <button type="submit" name="add_content_submit" class="btn btn-primary">
                <i class="bi bi-save-fill"></i> Lưu Toàn Bộ Nội Dung
              </button>
              <a href="course-management.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Quay lại Quản lý khóa học
              </a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Add/Edit Lesson -->
  <div class="modal fade" id="lessonModal" tabindex="-1" aria-labelledby="lessonModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="lessonModalLabel">Add Lesson</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <!-- Lesson Title -->
          <div class="mb-3">
            <label for="lesson-title" class="form-label">Tên Bài học</label>
            <input type="text" id="lesson-title" class="form-control" placeholder="Ví dụ: Bài 1 - Lời chào">
          </div>
          <!-- Content -->
          <div class="mb-3">
            <label class="form-label">Nội dung/Tóm tắt</label>
            <textarea id="lesson-content" class="form-control" rows="4"></textarea>
          </div>
          <!-- Feature Image -->
          <div class="mb-3">
            <label class="form-label">Feature Image (tùy chọn)</label>
            <input type="file" id="lesson-image" class="form-control" accept="image/*">
          </div>
          <!-- Video Source -->
          <div class="mb-3">
            <label for="video-source" class="form-label">Video Source</label>
            <select id="video-source" class="form-select">
              <option value="">Chọn nguồn video</option>
              <option value="mp4">HTML5 (mp4)</option>
              <option value="external">External URL</option>
              <option value="youtube">YouTube</option>
              <option value="vimeo">Vimeo</option>
              <option value="embedded">Embedded</option>
            </select>
          </div>
          <!-- URL/Embed -->
          <div id="video-url-group" class="mb-3" style="display:none;">
            <label for="video-url" class="form-label">Video URL / Embed</label>
            <input type="text" id="video-url" class="form-control" placeholder="https://... hoặc embed code">
          </div>
          <!-- MP4 File -->
          <div id="video-file-group" class="mb-3" style="display:none;">
            <label for="video-file" class="form-label">Upload MP4 File</label>
            <input type="file" id="video-file" class="form-control" accept="video/mp4">
          </div>
          <!-- Playback Time -->
          <label class="form-label">Video playback time</label>
          <div class="d-flex gap-2 mb-3">
            <input type="number" id="video-hh" class="form-control" placeholder="HH" min="0">
            <input type="number" id="video-mm" class="form-control" placeholder="MM" min="0" max="59">
            <input type="number" id="video-ss" class="form-control" placeholder="SS" min="0" max="59">
          </div>
          <!-- Attachments -->
          <div class="mb-3">
            <label for="lesson-attachments" class="form-label">Attachments (pdf, zip, ...)</label>
            <input type="file" id="lesson-attachments" class="form-control" multiple accept=".pdf,.zip,.doc,.ppt,.pptx">
          </div>
        </div>
        <div class="modal-footer">
          <button id="update-lesson" type="button" class="btn btn-primary">Update Lesson</button>
        </div>
      </div>
    </div>
  </div>

  <!-- JS -->
  <script src="js/jquery-3.7.1.slim.min.js"></script>
  <script src="js/bootstrap.bundle.min.js"></script>
  <script>
  $(function(){
    // Toggle thêm topic
    $('#btn-add-topic').on('click', () => $('#add-topic-form').collapse('toggle'));

    // Add topic vào DOM
    $('#save-topic').on('click', () => {
      const name  = $('#topic-name').val().trim();
      const sum   = $('#topic-summary').val().trim();
      if (!name) return alert('Vui lòng nhập tên chương');

      const id = 'topic-' + Date.now();
      const tpl = `
        <div class="card mb-2 topic-item" id="${id}">
          <div class="card-header d-flex justify-content-between align-items-center">
            <div><i class="bi bi-arrows-move me-2"></i>${name}</div>
            <div>
              <button class="btn btn-sm btn-outline-primary btn-add-lesson">+ Lesson</button>
              <button class="btn btn-sm btn-outline-secondary btn-add-quiz">+ Quiz</button>
            </div>
          </div>
          <ul class="list-group list-group-flush lessons"></ul>
        </div>`;
      $('#topics-list').append(tpl);
      $('#topic-name, #topic-summary').val('');
      $('#add-topic-form').collapse('hide');
    });

    // Mở modal Add Lesson
    $('#topics-list').on('click', '.btn-add-lesson', function(){
      const topicId = $(this).closest('.topic-item').attr('id');
      $('#lessonModal').data('topicId', topicId).modal('show');
    });

    // Hiển thị URL hay File picker
    $('#video-source').on('change', function(){
      if (this.value === 'mp4') {
        $('#video-file-group').show();
        $('#video-url-group').hide();
      } else {
        $('#video-file-group').hide();
        $('#video-url-group').show();
      }
    });

    // Reset modal khi đóng
    $('#lessonModal').on('hidden.bs.modal', function(){
      $(this).find('input,textarea').val('');
      $('#video-file-group,#video-url-group').hide();
      $('#video-source').val('');
    });

    // Update lesson vào đúng topic
    $('#update-lesson').on('click', () => {
      const m       = $('#lessonModal');
      const topicId = m.data('topicId');
      const title   = $('#lesson-title').val().trim();
      if (!title) return alert('Chưa nhập tiêu đề bài học');

      // Lấy attachments
      const files = $('#lesson-attachments')[0].files;
      let attachHtml = '';
      if (files.length) {
        attachHtml = '<ul class="mt-2 mb-0">';
        for (let f of files) {
          attachHtml += `<li><i class="bi bi-paperclip me-1"></i>${f.name}</li>`;
        }
        attachHtml += '</ul>';
      }

      const item = `
        <li class="list-group-item">
          <i class="bi bi-file-earmark-play me-2"></i>${title}
          ${attachHtml}
        </li>`;
      $('#' + topicId).find('.lessons').append(item);
      m.modal('hide');
    });

    // Optional: ngăn form submit để demo frontend
    $('#addContentForm').on('submit', e => {
      e.preventDefault();
      alert('Dữ liệu đã sẵn sàng gửi lên server!\nXem console để debug.');
      console.log('Course ID:', $('#course_id').val());
      $('#topics-list .topic-item').each(function(){
        console.log('Topic:', $(this).find('.card-header div').text().trim());
        $(this).find('.lessons li').each(function(){
          console.log('  Lesson:', $(this).text().trim());
        });
      });
    });
  });
  </script>
</body>
</html>
