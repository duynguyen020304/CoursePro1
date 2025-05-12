<?php
// File: service/service_review.php

require_once __DIR__ . '/../model/bll/review_bll.php';
require_once __DIR__ . '/../model/dto/review_dto.php';
require_once __DIR__ . '/service_response.php';

class ReviewService
{
    private ReviewBLL $bll;

    public function __construct()
    {
        $this->bll = new ReviewBLL();
    }

    /**
     * Thêm mới đánh giá
     */
    public function create_review(string $reviewID, string $userID, string $courseID, int $rating, ?string $comment): ServiceResponse
    {
        $dto = new ReviewDTO($reviewID, $userID, $courseID, $rating, $comment);
        $ok = $this->bll->create_review($dto);
        if ($ok) {
            return new ServiceResponse(true, 'Tạo đánh giá thành công', $dto);
        }
        return new ServiceResponse(false, 'Tạo đánh giá thất bại');
    }

    /**
     * Cập nhật đánh giá
     */
    public function update_review(string $reviewID, string $userID, string $courseID, int $rating, ?string $comment): ServiceResponse
    {
        $dto = new ReviewDTO($reviewID, $userID, $courseID, $rating, $comment);
        $ok = $this->bll->update_review($dto);
        if ($ok) {
            return new ServiceResponse(true, 'Cập nhật đánh giá thành công');
        }
        return new ServiceResponse(false, 'Cập nhật đánh giá thất bại');
    }

    /**
     * Xóa đánh giá
     */
    public function delete_review(string $reviewID): ServiceResponse
    {
        $ok = $this->bll->delete_review($reviewID);
        if ($ok) {
            return new ServiceResponse(true, 'Xóa đánh giá thành công');
        }
        return new ServiceResponse(false, 'Xóa đánh giá thất bại hoặc không tồn tại');
    }

    /**
     * Lấy đánh giá theo khóa học
     */
    public function get_reviews_by_course(string $courseID): ServiceResponse
    {
        $list = $this->bll->get_reviews_by_course($courseID);
        return new ServiceResponse(true, 'Lấy danh sách đánh giá thành công', $list);
    }
}


