<?php

use DateTime;
class CategoryDTO
{
    public int $id;
    public string $name;
    public ?int $parent_id;
    public int $sort_order;

    public function __construct(int $id, string $name, ?int $parent_id = null, int $sort_order = 0)
    {
        $this->id         = $id;
        $this->name       = $name;
        $this->parent_id  = $parent_id;
        $this->sort_order = $sort_order;
    }
}
