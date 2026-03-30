<?php
// Thay đổi đường dẫn để trỏ đến database_mysql.php
require_once __DIR__ . '/../Database/database.php';
require_once __DIR__ . '/../DTO/video_dto.php';

class VideoBLL extends Database
{
    /**
     * Tạo một video mới trong cơ sở dữ liệu.
     * @param VideoDTO $v Đối tượng DTO chứa thông tin video.
     * @return bool Trả về true nếu tạo thành công, ngược lại false.
     */
    public function create_video(VideoDTO $v): bool
    {
        // Câu lệnh SQL INSERT cho MySQL với các placeholder '?'
        $sql = "INSERT INTO CourseVideo (VideoID, LessonID, Url, Title, Duration, SortOrder) VALUES (?, ?, ?, ?, ?, ?)";
        
        // Mảng tham số cho prepared statement
        $params = [
            $v->videoID,
            $v->lessonID,
            $v->url,
            $v->title,
            $v->duration ?? 0,
            $v->sortOrder,
        ];

        // Thực thi câu lệnh và trả về kết quả dựa trên số dòng bị ảnh hưởng
        $this->executePrepared($sql, $params);
        return $this->getAffectedRows() > 0;
    }

    /**
     * Xóa một video khỏi cơ sở dữ liệu.
     * @param string $vid ID của video cần xóa.
     * @return bool Trả về true nếu xóa thành công, ngược lại false.
     */
    public function delete_video(string $vid): bool
    {
        $sql = "DELETE FROM CourseVideo WHERE VideoID = ?";
        $params = [$vid];
        $this->executePrepared($sql, $params);
        return $this->getAffectedRows() > 0;
    }

    /**
     * Cập nhật thông tin một video.
     * @param VideoDTO $v Đối tượng DTO chứa thông tin video cần cập nhật.
     * @return bool Trả về true nếu cập nhật thành công, ngược lại false.
     */
    public function update_video(VideoDTO $v): bool
    {
        $sql = "UPDATE CourseVideo SET LessonID = ?, Url = ?, SortOrder = ?, Title = ?, Duration = ? WHERE VideoID = ?";
        
        $params = [
            $v->lessonID,
            $v->url,
            $v->sortOrder,
            $v->title,
            $v->duration ?? 0,
            $v->videoID
        ];

        $this->executePrepared($sql, $params);
        return $this->getAffectedRows() > 0;
    }

    /**
     * Lấy thông tin một video bằng ID.
     * @param string $videoID ID của video.
     * @return VideoDTO|null Trả về đối tượng VideoDTO nếu tìm thấy, ngược lại null.
     */
    public function get_video(string $videoID): ?VideoDTO
    {
        // Sử dụng DATE_FORMAT cho MySQL thay vì TO_CHAR
        $sql = "SELECT VideoID, LessonID, Url, Title, SortOrder, Duration, 
                       DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s') AS created_at_formatted
                FROM CourseVideo
                WHERE VideoID = ?";
        $params = [$videoID];
        
        $result = $this->executePrepared($sql, $params);

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return new VideoDTO(
                $row['VideoID'],
                $row['LessonID'],
                $row['Url'],
                $row['Title'],
                isset($row['SortOrder']) ? (int)$row['SortOrder'] : 0,
                isset($row['Duration']) ? (int)$row['Duration'] : null,
                $row['created_at_formatted'] ?? null
            );
        }
        return null;
    }

    /**
     * Lấy danh sách các video thuộc về một bài học.
     * @param string $lessonID ID của bài học.
     * @return array Mảng các đối tượng VideoDTO.
     */
    public function get_videos_by_lesson(string $lessonID): array
    {
        $sql = "SELECT VideoID, LessonID, Url, Title, SortOrder, Duration, 
                       DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s') AS created_at_formatted
                FROM CourseVideo
                WHERE LessonID = ?
                ORDER BY SortOrder";
        $params = [$lessonID];
        
        $videos = [];
        $result = $this->executePrepared($sql, $params);

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $videos[] = new VideoDTO(
                    $row['VideoID'],
                    $row['LessonID'],
                    $row['Url'],
                    $row['Title'],
                    isset($row['SortOrder']) ? (int)$row['SortOrder'] : 0,
                    isset($row['Duration']) ? (int)$row['Duration'] : null,
                    $row['created_at_formatted'] ?? null
                );
            }
        }
        return $videos;
    }
}
