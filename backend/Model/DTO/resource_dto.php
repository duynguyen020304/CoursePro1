<?php

class ResourceDTO
{
    public string $resourceID;
    public string $lessonID;
    public string $resourcePath;
    public ?string $title;
    public int $sortOrder;
    public ?string $created_at;

    public function __construct(
        string $resourceID,
        string $lessonID,
        string $resourcePath,
        ?string $title = null,
        int $sortOrder = 0,
        ?string $created_at = null
    ) {
        $this->resourceID    = $resourceID;
        $this->lessonID      = $lessonID;
        $this->resourcePath  = $resourcePath;
        $this->title         = $title;
        $this->sortOrder     = $sortOrder;
        $this->created_at    = $created_at;
    }
}