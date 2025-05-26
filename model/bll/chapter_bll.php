<?php
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../dto/chapter_dto.php';

class ChapterBLL extends Database
{
    public function create_chapter(ChapterDTO $chapter): bool
    {
        $sql = "INSERT INTO COURSECHAPTER (ChapterID, CourseID, Title, Description, SortOrder)
                VALUES (:chapterID, :courseID, :title, :description, :sortOrder)";

        $bindParams = [
            ':chapterID'   => $chapter->chapterID,
            ':courseID'    => $chapter->courseID,
            ':title'       => $chapter->title,
            ':description' => $chapter->description,
            ':sortOrder'   => $chapter->sortOrder ?? 0,
        ];

        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false) && ($this->getAffectedRows() === 1);
    }

    public function update_chapter(ChapterDTO $chapter): bool
    {
        $sql = "UPDATE COURSECHAPTER SET
                    CourseID    = :courseID,
                    Title       = :title,
                    Description = :description,
                    SortOrder   = :sortOrder
                WHERE ChapterID = :chapterID_where";

        $bindParams = [
            ':courseID'    => $chapter->courseID,
            ':title'       => $chapter->title,
            ':description' => $chapter->description,
            ':sortOrder'   => $chapter->sortOrder ?? 0,
            ':chapterID_where' => $chapter->chapterID,
        ];

        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false);
    }

    public function delete_chapter(string $chapterID): bool
    {
        $sql = "DELETE FROM COURSECHAPTER WHERE ChapterID = :chapterID";
        $bindParams = [':chapterID' => $chapterID];

        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false) && ($this->getAffectedRows() === 1);
    }

    public function get_all_chapters(): array
    {
        $sql = "SELECT ChapterID, CourseID, Title, Description, SortOrder, 
                       TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS CREATED_AT_FORMATTED 
                FROM COURSECHAPTER 
                ORDER BY SortOrder ASC, Title ASC";

        $stid = $this->executePrepared($sql);
        $list = [];

        if ($stid) {
            while (($row = @oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $description = null;
                if (is_object($row['DESCRIPTION']) && method_exists($row['DESCRIPTION'], 'read')) {
                    $description = $row['DESCRIPTION']->read($row['DESCRIPTION']->size());
                } elseif (isset($row['DESCRIPTION'])) {
                    $description = $row['DESCRIPTION'];
                }
                $list[] = new ChapterDTO(
                    $row['CHAPTERID'],
                    $row['COURSEID'],
                    $row['TITLE'],
                    $description,
                    isset($row['SORTORDER']) ? (int)$row['SORTORDER'] : 0,
                    $row['CREATED_AT_FORMATTED'] ?? null
                );
            }
            @oci_free_statement($stid);
        }
        return $list;
    }


    public function get_chapter_by_id(string $chapterID): ?ChapterDTO
    {
        $sql = "SELECT ChapterID, CourseID, Title, Description, SortOrder, 
                       TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS CREATED_AT_FORMATTED 
                FROM COURSECHAPTER 
                WHERE ChapterID = :chapterID_param";
        $bindParams = [':chapterID_param' => $chapterID];

        $stid = $this->executePrepared($sql, $bindParams);
        $dto = null;

        if ($stid) {
            if (($row = @oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $description = null;
                if (is_object($row['DESCRIPTION']) && method_exists($row['DESCRIPTION'], 'read')) {
                    $description = $row['DESCRIPTION']->read($row['DESCRIPTION']->size());
                } elseif (isset($row['DESCRIPTION'])) {
                    $description = $row['DESCRIPTION'];
                }
                $dto = new ChapterDTO(
                    $row['CHAPTERID'],
                    $row['COURSEID'],
                    $row['TITLE'],
                    $description,
                    isset($row['SORTORDER']) ? (int)$row['SORTORDER'] : 0,
                    $row['CREATED_AT_FORMATTED'] ?? null
                );
            }
            @oci_free_statement($stid);
        }
        return $dto;
    }

    public function get_chapters_by_course_id(string $courseID): array
    {
        $sql = "SELECT ChapterID, CourseID, Title, Description, SortOrder, 
                       TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS CREATED_AT_FORMATTED 
                FROM COURSECHAPTER 
                WHERE CourseID = :courseID_param 
                ORDER BY SortOrder ASC";

        $bindParams = [':courseID_param' => $courseID];

        $stid = $this->executePrepared($sql, $bindParams);
        $list = [];

        if ($stid) {
            while (($row = @oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $description = null;
                if (is_object($row['DESCRIPTION']) && method_exists($row['DESCRIPTION'], 'read')) {
                    $description = $row['DESCRIPTION']->read($row['DESCRIPTION']->size());
                } elseif (isset($row['DESCRIPTION'])) {
                    $description = $row['DESCRIPTION'];
                }
                $list[] = new ChapterDTO(
                    $row['CHAPTERID'],
                    $row['COURSEID'],
                    $row['TITLE'],
                    $description,
                    isset($row['SORTORDER']) ? (int)$row['SORTORDER'] : 0,
                    $row['CREATED_AT_FORMATTED'] ?? null
                );
            }
            @oci_free_statement($stid);
        }
        return $list;
    }
}