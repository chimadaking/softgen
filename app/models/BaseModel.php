<?php
namespace App\Models;

use PDO;
use PDOException;

class BaseModel {
    protected $db;

    public function __construct() {
        $config = require APP_ROOT . '/config/database.php';
        $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        try {
            $this->db = new PDO($dsn, $config['username'], $config['password'], $options);
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }

    public function query(string $sql, array $params = []): \PDOStatement {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function exec(string $sql, array $params = []): bool {
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function fetchAll(string $sql, array $params = []): array {
        return $this->query($sql, $params)->fetchAll();
    }

    public function fetch(string $sql, array $params = []): mixed {
        return $this->query($sql, $params)->fetch();
    }

    public function fetchColumn(string $sql, array $params = [], int $col = 0): mixed {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchColumn($col);
    }

    public function lastInsertId(): string|false {
        return $this->db->lastInsertId();
    }
}