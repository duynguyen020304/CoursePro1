// service/service_chapter.php
<?php
require_once __DIR__ . '/../model/bll/chapter_bll.php';
require_once __DIR__ . '/../model/dto/chapter_dto.php';
require_once __DIR__ . '/service_response.php';

class ChapterService
{
    private ChapterBLL $chapterBll;

    public function __construct()
    {
        $this->chapterBll = new ChapterBLL();
    }

    public function get_all_chapters(): ServiceResponse
    {
        $list = $this->chapterBll->get_all_chapters();
        return new ServiceResponse(true, 'Lấy danh sách chương thành công', $list);
    }

    public function get_chapter_by_id(string $chapterID): ServiceResponse
    {
        $chap = $this->chapterBll->get_chapter_by_id($chapterID);
        if ($chap) {
            return new ServiceResponse(true, 'Lấy chương thành công', $chap);
        }
        return new ServiceResponse(false, 'Không tìm thấy chương');
    }

    public function create_chapter(string $courseID, string $title, ?string $description, int $sortOrder): ServiceResponse
    {
        $chapterID = str_replace('.', '_', uniqid('chapter', true));
        $dto = new ChapterDTO($chapterID, $courseID, $title, $description, $sortOrder);
        if ($this->chapterBll->create_chapter($dto)) {
            return new ServiceResponse(true, 'Tạo chương thành công', $dto);
        }
        return new ServiceResponse(false, 'Tạo chương thất bại');
    }

    public function update_chapter(string $chapterID, string $courseID, string $title, ?string $description, int $sortOrder): ServiceResponse
    {
        $existing = $this->chapterBll->get_chapter_by_id($chapterID);
        if (!$existing) {
            return new ServiceResponse(false, 'Không tìm thấy chương');
        }
        $dto = new ChapterDTO($chapterID, $courseID, $title, $description, $sortOrder);
        if ($this->chapterBll->update_chapter($dto)) {
            return new ServiceResponse(true, 'Cập nhật chương thành công', $dto);
        }
        return new ServiceResponse(false, 'Cập nhật chương thất bại');
    }

    public function delete_chapter(string $chapterID): ServiceResponse
    {
        $existing = $this->chapterBll->get_chapter_by_id($chapterID);
        if (!$existing) {
            return new ServiceResponse(false, 'Không tìm thấy chương');
        }
        if ($this->chapterBll->delete_chapter($chapterID)) {
            return new ServiceResponse(true, 'Xóa chương thành công');
        }
        return new ServiceResponse(false, 'Xóa chương thất bại');
    }
}
