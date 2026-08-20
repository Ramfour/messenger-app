<?php
declare(strict_types=1);

namespace App\Core;

abstract class Model
{
    protected \PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }
}
