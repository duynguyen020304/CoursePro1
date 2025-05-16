<?php

class ReviewDTO
{
    public string $reviewID;
    public string $userID;
    public string $courseID;
    public int $rating;
    public ?string $comment;

    public function __construct(string $reviewID, string $userID, string $courseID, int $rating, ?string $comment)
    {
        $this->reviewID = $reviewID;
        $this->userID   = $userID;
        $this->courseID = $courseID;
        $this->rating   = $rating;
        $this->comment  = $comment;
    }
}
