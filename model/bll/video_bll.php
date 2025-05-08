<?php
require_once("../database.php");
require_once("../dto/video_dto.php");
class VideoBLL extends Database
{
    public function create_video(VideoDTO $v)
    {
        $title = $v->title ? "'{$v->title}'" : 'NULL';
        $sql = "INSERT INTO Video (VideoID, LessonID, Url, Title, SortOrder) VALUES ('{$v->videoID}', '{$v->lessonID}', '{$v->url}', {$title}, {$v->sortOrder})";
        $this->execute($sql);
        $this->close();
    }

    public function delete_video(string $vid)
    {
        $sql = "DELETE FROM Video WHERE VideoID = '{$vid}'";
        $this->execute($sql);
        $this->close();
    }

    public function update_video(VideoDTO $v)
    {
        $title = $v->title ? "Title = '{$v->title}'," : '';
        $sql = "UPDATE Video SET LessonID = '{$v->lessonID}', Url = '{$v->url}', {$title} SortOrder = {$v->sortOrder} WHERE VideoID = '{$v->videoID}'";
        $this->execute($sql);
        $this->close();
    }

    public function get_videos_by_lesson(string $lessonID): array
    {
        $sql = "SELECT * FROM Video WHERE LessonID = '{$lessonID}' ORDER BY SortOrder";
        $result = $this->execute($sql);
        $videos = [];
        while ($row = $result->fetch_assoc()) {
            $videos[] = new VideoDTO($row['VideoID'], $row['LessonID'], $row['Url'], $row['Title'], (int)$row['SortOrder']);
        }
        $this->close();
        return $videos;
    }
}
