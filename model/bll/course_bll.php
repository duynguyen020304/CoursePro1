<?php
// Thay đổi đường dẫn để trỏ đến tệp kết nối MySQL
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../dto/course_dto.php';

class CourseBLL extends Database
{
    /**
     * Chuyển đổi một hàng dữ liệu từ database thành một đối tượng CourseDTO.
     * @param array $row Mảng kết hợp chứa dữ liệu của một khóa học.
     * @return CourseDTO Đối tượng DTO của khóa học.
     */
    private function _map_row_to_dto(array $row): CourseDTO
    {
        return new CourseDTO(
            $row['courseID'],
            $row['title'],
            $row['description'],
            isset($row['price']) ? (float)$row['price'] : 0.0,
            $row['difficulty'],
            $row['language'],
            $row['createdBy'],
            // Sử dụng tên cột đã được định dạng trong câu lệnh SELECT
            $row['createdAt_formatted'] ?? null
        );
    }

    /**
     * Tạo một khóa học mới trong database.
     * @param CourseDTO $c Đối tượng chứa thông tin khóa học cần tạo.
     * @return bool Trả về true nếu tạo thành công, ngược lại false.
     */
    public function create_course(CourseDTO $c): bool
    {
        // Câu lệnh INSERT chuẩn của MySQL
        $sql = "INSERT INTO Course (courseID, title, description, price, difficulty, language, createdBy) VALUES (?, ?, ?, ?, ?, ?, ?)";

        // Tham số cho prepared statement
        $bindParams = [
            $c->courseID,
            $c->title,
            $c->description,
            is_numeric($c->price) ? (float)$c->price : 0,
            $c->difficulty,
            $c->language,
            $c->createdBy,
        ];

        // Thực thi câu lệnh
        $result = $this->executePrepared($sql, $bindParams);
        return ($result !== false && $this->getAffectedRows() > 0);
    }

    /**
     * Xóa một khóa học khỏi database.
     * @param string $courseID ID của khóa học cần xóa.
     * @return bool Trả về true nếu xóa thành công, ngược lại false.
     */
    public function delete_course(string $courseID): bool
    {
        // Câu lệnh DELETE chuẩn của MySQL
        $sql = "DELETE FROM Course WHERE courseID = ?";
        $bindParams = [$courseID];

        $result = $this->executePrepared($sql, $bindParams);
        return ($result !== false && $this->getAffectedRows() > 0);
    }

    /**
     * Cập nhật thông tin của một khóa học.
     * @param CourseDTO $c Đối tượng chứa thông tin khóa học cần cập nhật.
     * @return bool Trả về true nếu cập nhật thành công, ngược lại false.
     */
    public function update_course(CourseDTO $c): bool
    {
        // Câu lệnh UPDATE chuẩn của MySQL
        $sql = "UPDATE Course SET title = ?, description = ?, price = ?, difficulty = ?, language = ? WHERE courseID = ?";

        $bindParams = [
            $c->title,
            $c->description,
            is_numeric($c->price) ? (float)$c->price : 0,
            $c->difficulty,
            $c->language,
            $c->courseID, // Tham số cho mệnh đề WHERE
        ];

        $result = $this->executePrepared($sql, $bindParams);
        return ($result !== false);
    }

    /**
     * Lấy thông tin chi tiết của một khóa học bằng ID.
     * @param string $courseID ID của khóa học.
     * @return ?CourseDTO Trả về đối tượng CourseDTO nếu tìm thấy, ngược lại null.
     */
    public function get_course(string $courseID): ?CourseDTO
    {
        // Câu lệnh SELECT với định dạng ngày tháng
        $sql = "SELECT courseID, title, description, price, difficulty, language, createdBy, DATE_FORMAT(created_at, '%d-%m-%Y %H:%i:%s') as createdAt_formatted FROM Course WHERE courseID = ?";
        $bindParams = [$courseID];

        $result = $this->executePrepared($sql, $bindParams);

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $this->_map_row_to_dto($row);
        }

        return null;
    }

    /**
     * Lấy tất cả các khóa học từ database.
     * @return array Danh sách các đối tượng CourseDTO.
     */
    public function get_all_courses(): array
    {
        $sql = "SELECT courseID, title, description, price, difficulty, language, createdBy, DATE_FORMAT(created_at, '%d-%m-%Y %H:%i:%s') as createdAt_formatted FROM Course";
        $list = [];
        $result = $this->execute($sql); // Không cần prepared statement vì không có tham số

        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $list[] = $this->_map_row_to_dto($row);
            }
        }
        return $list;
    }

    /**
     * Tìm kiếm khóa học theo tiêu đề và các bộ lọc tùy chọn.
     * @param string $title Tiêu đề cần tìm.
     * @param ?string $difficulty Độ khó (tùy chọn).
     * @param ?string $language Ngôn ngữ (tùy chọn).
     * @return array Danh sách các khóa học phù hợp.
     */
    public function search_courses_by_title(string $title, ?string $difficulty = null, ?string $language = null): array
    {
        $sql = "SELECT courseID, title, description, price, difficulty, language, createdBy, DATE_FORMAT(created_at, '%d-%m-%Y %H:%i:%s') as createdAt_formatted FROM Course WHERE title LIKE ?";
        $bindParams = ["%{$title}%"];
        $list = [];

        if ($difficulty !== null && $difficulty !== '') {
            $sql .= " AND difficulty = ?";
            $bindParams[] = $difficulty;
        }

        if ($language !== null && $language !== '') {
            $sql .= " AND language = ?";
            $bindParams[] = $language;
        }

        $result = $this->executePrepared($sql, $bindParams);
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $list[] = $this->_map_row_to_dto($row);
            }
        }
        return $list;
    }

    /**
     * Lấy khóa học theo độ khó và ngôn ngữ.
     * @param string $difficulty Độ khó.
     * @param string $language Ngôn ngữ.
     * @return array Danh sách các khóa học phù hợp.
     */
    public function get_courses_by_difficulty_lang(string $difficulty, string $language): array
    {
        $sql = "SELECT courseID, title, description, price, difficulty, language, createdBy, DATE_FORMAT(created_at, '%d-%m-%Y %H:%i:%s') as createdAt_formatted FROM courses WHERE difficulty = ? AND language = ?";
        $bindParams = [$difficulty, $language];
        $list = [];

        $result = $this->executePrepared($sql, $bindParams);
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $list[] = $this->_map_row_to_dto($row);
            }
        }
        return $list;
    }

    /**
     * Lấy khóa học theo ngôn ngữ.
     * @param string $language Ngôn ngữ.
     * @return array Danh sách các khóa học phù hợp.
     */
    public function get_courses_by_language(string $language): array
    {
        $sql = "SELECT courseID, title, description, price, difficulty, language, createdBy, DATE_FORMAT(created_at, '%d-%m-%Y %H:%i:%s') as createdAt_formatted FROM Course WHERE language = ?";
        $bindParams = [$language];
        $list = [];

        $result = $this->executePrepared($sql, $bindParams);
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $list[] = $this->_map_row_to_dto($row);
            }
        }
        return $list;
    }

    /**
     * Lấy khóa học theo độ khó.
     * @param string $difficulty Độ khó.
     * @return array Danh sách các khóa học phù hợp.
     */
    public function get_courses_by_difficulty(string $difficulty): array
    {
        $sql = "SELECT courseID, title, description, price, difficulty, language, createdBy, DATE_FORMAT(created_at, '%d-%m-%Y %H:%i:%s') as createdAt_formatted FROM Course WHERE difficulty = ?";
        $bindParams = [$difficulty];
        $list = [];

        $result = $this->executePrepared($sql, $bindParams);
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $list[] = $this->_map_row_to_dto($row);
            }
        }
        return $list;
    }

    /**
     * Lấy danh sách khóa học có phân trang và bộ lọc.
     * @param int $pageNumber Số trang hiện tại.
     * @param int $pageSize Số lượng khóa học trên mỗi trang.
     * @param ?string $filterDifficulty Lọc theo độ khó (tùy chọn).
     * @param ?string $filterLanguage Lọc theo ngôn ngữ (tùy chọn).
     * @return array Danh sách các khóa học trên trang hiện tại.
     */
    public function get_courses_paginated(int $pageNumber, int $pageSize = 10, ?string $filterDifficulty = null, ?string $filterLanguage = null): array
    {
        $offset = ($pageNumber - 1) * $pageSize;
        $whereClauses = [];
        $bindParams = [];
        $list = [];

        $sql = "SELECT courseID, title, description, price, difficulty, language, createdBy, DATE_FORMAT(created_at, '%d-%m-%Y %H:%i:%s') as createdAt_formatted FROM Course";

        if ($filterDifficulty !== null && $filterDifficulty !== '') {
            $whereClauses[] = "difficulty = ?";
            $bindParams[] = $filterDifficulty;
        }

        if ($filterLanguage !== null && $filterLanguage !== '') {
            $whereClauses[] = "language = ?";
            $bindParams[] = $filterLanguage;
        }

        if (!empty($whereClauses)) {
            $sql .= " WHERE " . implode(" AND ", $whereClauses);
        }

        $sql .= " LIMIT ? OFFSET ?";
        $bindParams[] = $pageSize;
        $bindParams[] = $offset;

        $result = $this->executePrepared($sql, $bindParams);
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $list[] = $this->_map_row_to_dto($row);
            }
        }
        return $list;
    }
}
