<?php
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../dto/video_dto.php';

class VideoBLL extends Database
{
    public function create_video(VideoDTO $v): bool
    {
        $sql = "INSERT INTO CourseVideo (VideoID, LessonID, Url, Title, Duration, SortOrder) 
                VALUES (:videoID, :lessonID, :url, :title, :duration, :sortOrder)";
        $bindParams = [
            ':videoID'   => $v->videoID,
            ':lessonID'  => $v->lessonID,
            ':url'       => $v->url,
            ':title'     => $v->title,
            ':duration'  => $v->duration ?? 0,
            ':sortOrder' => $v->sortOrder,
        ];
        $stid = $this->executePrepared($sql, $bindParams);
        $success = ($stid !== false);
        if ($success) {
        }
        return $success && $this->getAffectedRows() === 1;
    }

    public function delete_video(string $vid): bool
    {
        $sql = "DELETE FROM CourseVideo WHERE VideoID = :videoID";
        $bindParams = [':videoID' => $vid];
        $stid = $this->executePrepared($sql, $bindParams);
        $success = ($stid !== false);
        return $success && $this->getAffectedRows() === 1;
    }

    public function update_video(VideoDTO $v): bool
    {
        $setClauses = [];
        $bindParams = [];
        $setClauses[] = "LessonID = :lessonID";
        $bindParams[':lessonID'] = $v->lessonID;
        $setClauses[] = "Url = :url";
        $bindParams[':url'] = $v->url;
        $setClauses[] = "SortOrder = :sortOrder";
        $bindParams[':sortOrder'] = $v->sortOrder;
        $setClauses[] = "Title = :title";
        $bindParams[':title'] = $v->title;
        $setClauses[] = "Duration = :duration";
        $bindParams[':duration'] = $v->duration ?? 0;
        $bindParams[':videoID_where'] = $v->videoID;
        if (empty($setClauses)) {
            return true;
        }
        $sql = "UPDATE CourseVideo SET " . implode(', ', $setClauses) . " WHERE VideoID = :videoID_where";
        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false);
    }

    public function get_video(string $videoID): ?VideoDTO
    {
        $sql = "SELECT VideoID, LessonID, Url, Title, SortOrder, Duration 
                FROM CourseVideo 
                WHERE VideoID = :videoID_param";
        $bindParams = [':videoID_param' => $videoID];
        $stid = $this->executePrepared($sql, $bindParams);
        if ($stid) {
            if (($row = @oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $video = new VideoDTO(
                    $row['VIDEOID'],
                    $row['LESSONID'],
                    $row['URL'],
                    $row['TITLE'],
                    isset($row['SORTORDER']) ? (int)$row['SORTORDER'] : 0,
                    isset($row['DURATION']) ? (int)$row['DURATION'] : null
                );
                @oci_free_statement($stid);
                return $video;
            }
            @oci_free_statement($stid);
        }
        return null;
    }

    public function get_videos_by_lesson(string $lessonID): array
    {
        $sql = "SELECT VideoID, LessonID, Url, Title, SortOrder, Duration 
                FROM CourseVideo 
                WHERE LessonID = :lessonID_param 
                ORDER BY SortOrder";
        $bindParams = [':lessonID_param' => $lessonID];
        $stid = $this->executePrepared($sql, $bindParams);
        $videos = [];
        if ($stid) {
            while (($row = @oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $videos[] = new VideoDTO(
                    $row['VIDEOID'],
                    $row['LESSONID'],
                    $row['URL'],
                    $row['TITLE'],
                    isset($row['SORTORDER']) ? (int)$row['SORTORDER'] : 0,
                    isset($row['DURATION']) ? (int)$row['DURATION'] : null
                );
            }
            @oci_free_statement($stid);
        }
        return $videos;
    }
}
