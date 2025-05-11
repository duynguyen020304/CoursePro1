<?php
// File: service/service_video.php

require_once __DIR__ . '/../model/bll/video_bll.php';
require_once __DIR__ . '/../model/dto/video_dto.php';
require_once __DIR__ . '/service_response.php';

class VideoService
{
    private VideoBLL $bll;

    public function __construct()
    {
        $this->bll = new VideoBLL();
    }

    /** Lấy một video theo ID */
    public function get_video(string $videoID): ServiceResponse
    {
        try {
            $dto = $this->bll->get_video($videoID);
            if ($dto) {
                return new ServiceResponse(true, 'Lấy video thành công', $dto);
            }
            return new ServiceResponse(false, 'Video không tồn tại');
        } catch (Exception $e) {
            return new ServiceResponse(false, 'Lỗi khi lấy video: ' . $e->getMessage());
        }
    }

    /** Lấy danh sách video của một lesson */
    public function get_videos_by_lesson(string $lessonID): ServiceResponse
    {
        try {
            $list = $this->bll->get_videos_by_lesson($lessonID);
            return new ServiceResponse(true, 'Lấy danh sách video thành công', $list);
        } catch (Exception $e) {
            return new ServiceResponse(false, 'Lỗi khi lấy danh sách: ' . $e->getMessage());
        }
    }

    /** Tạo mới video */
    public function create_video(string $lessonID, string $url, ?string $title, int $sortOrder): ServiceResponse
    {
        $videoID = uniqid('video_', true);
        $dto = new VideoDTO($videoID, $lessonID, $url, $title, $sortOrder);
        try {
            $ok = $this->bll->create_video($dto);
            if ($ok) {
                return new ServiceResponse(true, 'Tạo video thành công', $dto);
            }
            return new ServiceResponse(false, 'Tạo video thất bại');
        } catch (Exception $e) {
            return new ServiceResponse(false, 'Lỗi khi tạo video: ' . $e->getMessage());
        }
    }

    /** Cập nhật video */
    public function update_video(string $videoID, string $lessonID, string $url, ?string $title, int $sortOrder): ServiceResponse
    {
        $dto = new VideoDTO($videoID, $lessonID, $url, $title, $sortOrder);
        try {
            $ok = $this->bll->update_video($dto);
            if ($ok) {
                return new ServiceResponse(true, 'Cập nhật video thành công');
            }
            return new ServiceResponse(false, 'Cập nhật video thất bại');
        } catch (Exception $e) {
            return new ServiceResponse(false, 'Lỗi khi cập nhật: ' . $e->getMessage());
        }
    }

    /** Xóa video */
    public function delete_video(string $videoID): ServiceResponse
    {
        try {
            // Kiểm tra tồn tại
            $dto = $this->bll->get_video($videoID);
            if (!$dto) {
                return new ServiceResponse(false, 'Video không tồn tại');
            }
            $this->bll->delete_video($videoID);
            return new ServiceResponse(true, 'Xóa video thành công');
        } catch (Exception $e) {
            return new ServiceResponse(false, 'Lỗi khi xóa video: ' . $e->getMessage());
        }
    }
}
