<?php
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../dto/chapter_dto.php';

class ChapterBLL extends Database
{
    public function get_all_chapters()
    {
        $rows = $this->fetchAll("SELECT * FROM `Chapter` ORDER BY SortOrder ASC");
        $list = [];
        foreach ($rows as $row) {
            $list[] = new ChapterDTO(
                $row['ChapterID'],
                $row['CourseID'],
                $row['Title'],
                $row['Description'],
                (int)$row['SortOrder']
            );
        }
        return $list;
    }

    public function get_chapter_by_id(string $chapterID)
    {
        $rows = $this->fetchRow("SELECT * FROM `Chapter` WHERE ChapterID = '{$chapterID}'");
        if (!$rows) {
            return null;
        }
        $list = [];
        foreach ($rows as $row) {
            $list[] = new ChapterDTO(
                $row['ChapterID'],
                $row['CourseID'],
                $row['Title'],
                $row['Description'],
                (int)$row['SortOrder']
            );
        }
        return $list;
    }

    public function get_chapter_by_courseID(string $courseID): array
    {
        $sql = "SELECT * FROM `Chapter` WHERE CourseID = '{$courseID}' ORDER BY SortOrder ASC";
        $rows = $this->execute($sql);
        $list = [];
        while ($row = $rows->fetch_assoc()) {
            $list[] = new ChapterDTO(
                $row['ChapterID'],
                $row['CourseID'],
                $row['Title'],
                $row['Description'],
                (int)$row['SortOrder']
            );
        }
        return $list;
    }

//    public function get_all_categories(): array
//    {
//        $sql = "SELECT * FROM categories ORDER BY sort_order ASC, name ASC";
//        $result = $this->execute($sql);
//        $list = [];
//        while ($row = $result->fetch_assoc()) {
//            $list[] = new CategoryDTO(
//                (int)$row['id'],
//                $row['name'],
//                isset($row['parent_id']) ? (int)$row['parent_id'] : null,
//                (int)$row['sort_order']
//            );
//        }
//        // $this->close();
//        return $list;
//    }

    public function create_chapter(ChapterDTO $chapter)
    {
        $desc = $chapter->description !== null
            ? "'" . $this->conn->real_escape_string($chapter->description) . "'"
            : "NULL";
        $sql = "INSERT INTO `Chapter` (ChapterID, CourseID, Title, Description, SortOrder)
                VALUES (
                  '{$chapter->chapterID}',
                  '{$chapter->courseID}',
                  '{$this->conn->real_escape_string($chapter->title)}',
                  {$desc},
                  {$chapter->sortOrder}
                )";
        return $this->execute($sql) === true;
    }

    public function update_chapter(ChapterDTO $chapter)
    {
        $desc = $chapter->description !== null
            ? "'" . $this->conn->real_escape_string($chapter->description) . "'"
            : "NULL";
        $sql = "UPDATE `Chapter` SET
                    CourseID    = '{$chapter->courseID}',
                    Title       = '{$this->conn->real_escape_string($chapter->title)}',
                    Description = {$desc},
                    SortOrder   = {$chapter->sortOrder}
                WHERE ChapterID = '{$chapter->chapterID}'";
        return $this->execute($sql) === true;
    }

    public function delete_chapter(string $chapterID)
    {
        $sql = "DELETE FROM `Chapter` WHERE ChapterID = '{$chapterID}'";
        return $this->execute($sql) === true;
    }
}
