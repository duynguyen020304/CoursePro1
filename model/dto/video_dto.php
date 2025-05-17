<?php

class VideoDTO
{
    public string $videoID;
    public string $lessonID;
    public string $url;
    public ?string $title;
    public int $sortOrder;
    public ?int $duration; // thêm trường duration kiểu int (giá trị giây)

    public function __construct(string $videoID, string $lessonID, string $url, ?string $title, int $sortOrder, ?int $duration)
    {
        $this->videoID   = $videoID;
        $this->lessonID  = $lessonID;
        $this->url       = $url;
        $this->title     = $title;
        $this->sortOrder = $sortOrder;
        $this->duration  = $duration;
    }
}