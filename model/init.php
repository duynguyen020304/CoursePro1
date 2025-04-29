<?php


require_once("database.php");

class InitDatabase extends Database
{
    public function create_structure(): void
    {
        $sql = file_get_contents(__DIR__ . '/schema.sql');
        if ($this->runScript($sql)) {
            echo "INIT COMPLETE";
        }
        else {
            echo "INIT FAILED";
        }
    }
}

$myinit = new initDatabase();
$myinit->create_structure();
