<?php
/**
 * Cryonix Panel - Database Connection
 * Copyright 2026 XProject-Hub
 */

namespace CryonixPanel\Core;

class Database {
    private static ?Database $instance = null;
    private \PDO $pdo;
    
    private function __construct() {
        $config = require __DIR__ . '/../config/database.php';
        
        $dsn = sprintf(
            "%s:host=%s;port=%d;dbname=%s;charset=%s",
            $config['driver'],
            $config['host'],
            $config['port'],
            $config['database'],
            $config['charset']
        );
        
        $this->pdo = new \PDO($dsn, $config['username'], $config['password'], [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }
    
    public static function getInstance(): Database {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection(): \PDO {
        return $this->pdo;
    }
    
    public function query(string $sql, array $params = []): \PDOStatement {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
    
    public function fetch(string $sql, array $params = []): ?array {
        $result = $this->query($sql, $params)->fetch();
        return $result ?: null;
    }
    
    public function fetchAll(string $sql, array $params = []): array {
        return $this->query($sql, $params)->fetchAll();
    }
    
    public function insert(string $table, array $data): int {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $this->query("INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})", array_values($data));
        return (int) $this->pdo->lastInsertId();
    }
    
    public function update(string $table, array $data, string $where, array $whereParams = []): int {
        $set = implode(' = ?, ', array_keys($data)) . ' = ?';
        return $this->query("UPDATE {$table} SET {$set} WHERE {$where}", array_merge(array_values($data), $whereParams))->rowCount();
    }
    
    public function delete(string $table, string $where, array $params = []): int {
        return $this->query("DELETE FROM {$table} WHERE {$where}", $params)->rowCount();
    }
    
    public function count(string $table, string $where = '1=1', array $params = []): int {
        return (int) $this->fetch("SELECT COUNT(*) as cnt FROM {$table} WHERE {$where}", $params)['cnt'];
    }
}

