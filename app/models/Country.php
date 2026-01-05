<?php
namespace App\Models;

class Country extends BaseModel
{
    public function create(string $code, string $name): bool
    {
        $sql = "INSERT IGNORE INTO countries (code, name) VALUES (?, ?)";
        return (bool)$this->exec($sql, [$code, $name]);
    }

    public function getByCode(string $code): mixed
    {
        $sql = "SELECT id FROM countries WHERE code = ?";
        return $this->fetch($sql, [$code]);
    }
}