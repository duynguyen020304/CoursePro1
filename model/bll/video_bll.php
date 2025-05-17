<?php
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../dto/video_dto.php';
class VideoBLL extends Database
{
    public function create_video(VideoDTO $v)
    {
        $title = $v->title ? "'{$v->title}'" : 'NULL';
        $duration = $v->duration !== null ? intval($v->duration) : 0;
        $sql = "INSERT INTO CourseVideo (VideoID, LessonID, Url, Title, Duration, SortOrder) VALUES ('{$v->videoID}', '{$v->lessonID}', '{$v->url}', {$title},  {$duration}, {$v->sortOrder})";
        $result = $this->execute($sql);
        return $result === true && $this->getAffectedRows() === 1;
    }

    public function delete_video(string $vid)
    {
        $sql = "DELETE FROM CourseVideo WHERE VideoID = '{$vid}'";
        $result = $this->execute($sql);
        return $result === true && $this->getAffectedRows() === 1;
    }

    public function update_video(VideoDTO $v)
    {
        $title = $v->title ? "Title = '{$v->title}'," : '';
        $duration = $v->duration !== null ? "Duration = {$v->duration}," : '';
        $sql = "UPDATE CourseVideo SET LessonID = '{$v->lessonID}', Url = '{$v->url}', {$title} {$duration} SortOrder = {$v->sortOrder} WHERE VideoID = '{$v->videoID}'";
        $result = $this->execute($sql);
        return $result === true;
    }

    public function get_video(string $videoID): ?VideoDTO
    {
        $sql = "SELECT * FROM CourseVideo WHERE VideoID = '{$videoID}'";
        $result = $this->execute($sql);
        if ($row = $result->fetch_assoc()) {
            return new VideoDTO(
                $row['VideoID'],
                $row['LessonID'],
                $row['Url'],
                $row['Title'],
                (int)$row['SortOrder'],
                isset($row['Duration']) ? (int)$row['Duration'] : null
            );
        }
        return null;
    }

    public function get_videos_by_lesson(string $lessonID): array
    {
        $sql = "SELECT * FROM CourseVideo WHERE LessonID = '{$lessonID}' ORDER BY SortOrder";
        $result = $this->execute($sql);
        $videos = [];
        while ($row = $result->fetch_assoc()) {
            $videos[] = new VideoDTO(
                $row['VideoID'],
                $row['LessonID'],
                $row['Url'],
                $row['Title'],
                (int)$row['SortOrder'],
                isset($row['Duration']) ? (int)$row['Duration'] : null
            );
        }
        return $videos;
    }
}