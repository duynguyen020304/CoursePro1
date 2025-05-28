<?php
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../dto/video_dto.php';

class VideoBLL extends Database
{
    public function create_video(VideoDTO $v): bool
    {
        $sql = "BEGIN COURSE_VIDEO_PKG.CREATE_VIDEO_PROC(:videoID, :lessonID, :url, :title, :duration, :sortOrder); END;";
        $bindParams = [
            ':videoID'   => $v->videoID,
            ':lessonID'  => $v->lessonID,
            ':url'       => $v->url,
            ':title'     => $v->title,
            ':duration'  => $v->duration ?? 0,
            ':sortOrder' => $v->sortOrder ?? 0,
        ];
        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false);
    }

    public function delete_video(string $vid): bool
    {
        $sql = "BEGIN COURSE_VIDEO_PKG.DELETE_VIDEO_PROC(:videoID); END;";
        $bindParams = [':videoID' => $vid];
        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false);
    }

    public function update_video(VideoDTO $v): bool
    {
        $sql = "BEGIN COURSE_VIDEO_PKG.UPDATE_VIDEO_PROC(:videoID_where, :lessonID, :url, :title, :duration, :sortOrder); END;";
        $bindParams = [
            ':videoID_where' => $v->videoID,
            ':lessonID'  => $v->lessonID,
            ':url'       => $v->url,
            ':title'     => $v->title,
            ':duration'  => $v->duration ?? 0,
            ':sortOrder' => $v->sortOrder ?? 0,
        ];
        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false);
    }

    public function get_video(string $videoID): ?VideoDTO
    {
        $sql = "BEGIN :result_cursor := COURSE_VIDEO_PKG.GET_VIDEO_BY_ID_FUNC(:videoID_param); END;";
        $bindParams = [
            ':videoID_param' => $videoID
        ];

        $dto = null;
        $out_cursor = @oci_new_cursor($this->conn);
        if (!$out_cursor) {
            error_log('[VideoBLL] Failed to create new cursor for GET_VIDEO_BY_ID_FUNC: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            return null;
        }

        $parsed_stid = @oci_parse($this->conn, $sql);
        if (!$parsed_stid) {
            error_log('[VideoBLL] OCI Parse failed for GET_VIDEO_BY_ID_FUNC. SQL: ' . $sql . ' Error: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            @oci_free_cursor($out_cursor);
            return null;
        }

        @oci_bind_by_name($parsed_stid, ':videoID_param', $bindParams[':videoID_param']);
        @oci_bind_by_name($parsed_stid, ':result_cursor', $out_cursor, -1, OCI_B_CURSOR);

        $execute_mode = ($this->inTransaction) ? OCI_NO_AUTO_COMMIT : OCI_DEFAULT;
        if (!@oci_execute($parsed_stid, $execute_mode)) {
            error_log('[VideoBLL] OCI Execute failed for GET_VIDEO_BY_ID_FUNC block. Error: ' . ($parsed_stid ? oci_error($parsed_stid)['message'] : 'No statement handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return null;
        }

        if (!@oci_execute($out_cursor, $execute_mode)) {
            error_log('[VideoBLL] OCI Execute failed for result cursor of GET_VIDEO_BY_ID_FUNC. Error: ' . ($out_cursor ? oci_error($out_cursor)['message'] : 'No cursor handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return null;
        }
        $stid_cursor = $out_cursor;

        if ($stid_cursor) {
            if (($row = @oci_fetch_array($stid_cursor, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $dto = new VideoDTO(
                    $row['VIDEOID'],
                    $row['LESSONID'],
                    $row['URL'],
                    $row['TITLE'],
                    isset($row['SORTORDER']) ? (int)$row['SORTORDER'] : 0,
                    isset($row['DURATION']) ? (int)$row['DURATION'] : null,
                    $row['CREATED_AT_FORMATTED'] ?? null
                );
            }
            @oci_free_statement($stid_cursor);
        }
        @oci_free_statement($parsed_stid);

        return $dto;
    }

    public function get_videos_by_lesson(string $lessonID): array
    {
        $sql = "BEGIN :result_cursor := COURSE_VIDEO_PKG.GET_VIDEOS_BY_LESSON_FUNC(:lessonID_param); END;";
        $bindParams = [
            ':lessonID_param' => $lessonID
        ];
        $videos = [];
        $out_cursor = @oci_new_cursor($this->conn);
        if (!$out_cursor) {
            error_log('[VideoBLL] Failed to create new cursor for GET_VIDEOS_BY_LESSON_FUNC: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            return [];
        }

        $parsed_stid = @oci_parse($this->conn, $sql);
        if (!$parsed_stid) {
            error_log('[VideoBLL] OCI Parse failed for GET_VIDEOS_BY_LESSON_FUNC. SQL: ' . $sql . ' Error: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            @oci_free_cursor($out_cursor);
            return [];
        }

        @oci_bind_by_name($parsed_stid, ':lessonID_param', $bindParams[':lessonID_param']);
        @oci_bind_by_name($parsed_stid, ':result_cursor', $out_cursor, -1, OCI_B_CURSOR);

        $execute_mode = ($this->inTransaction) ? OCI_NO_AUTO_COMMIT : OCI_DEFAULT;
        if (!@oci_execute($parsed_stid, $execute_mode)) {
            error_log('[VideoBLL] OCI Execute failed for GET_VIDEOS_BY_LESSON_FUNC block. Error: ' . ($parsed_stid ? oci_error($parsed_stid)['message'] : 'No statement handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return [];
        }

        if (!@oci_execute($out_cursor, $execute_mode)) {
            error_log('[VideoBLL] OCI Execute failed for result cursor of GET_VIDEOS_BY_LESSON_FUNC. Error: ' . ($out_cursor ? oci_error($out_cursor)['message'] : 'No cursor handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return [];
        }
        $stid_cursor = $out_cursor;

        if ($stid_cursor) {
            while (($row = @oci_fetch_array($stid_cursor, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $videos[] = new VideoDTO(
                    $row['VIDEOID'],
                    $row['LESSONID'],
                    $row['URL'],
                    $row['TITLE'],
                    isset($row['SORTORDER']) ? (int)$row['SORTORDER'] : 0,
                    isset($row['DURATION']) ? (int)$row['DURATION'] : null,
                    $row['CREATED_AT_FORMATTED'] ?? null
                );
            }
            @oci_free_statement($stid_cursor);
        }
        @oci_free_statement($parsed_stid);

        return $videos;
    }
}
?>