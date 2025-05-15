<?php

class VideoDTO
{
    public string $videoID;
    public string $lessonID;
    public string $url;
    public ?string $title;
    public int $sortOrder;

    public function __construct(string $videoID, string $lessonID, string $url, ?string $title, int $sortOrder)
    {
        $this->videoID   = $videoID;
        $this->lessonID  = $lessonID;
        $this->url       = $url;
        $this->title     = $title;
        $this->sortOrder = $sortOrder;
    }
}
