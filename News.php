<?php
session_start();
require_once __DIR__ . '/config.php';

class News
{
    public int $id;
    public string $title;
    public string $body;
    public string $created_at;
    public string $updated_at;

    public static function all(): array
    {
        $pdo = getPDO();
        $stmt = $pdo->query('SELECT * FROM news ORDER BY id DESC');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function find(int $id): ?array
    {
        $pdo = getPDO();
        $stmt = $pdo->prepare('SELECT * FROM news WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function create(string $title, string $body): int
    {
        $pdo = getPDO();
        $stmt = $pdo->prepare('INSERT INTO news (title, body) VALUES (:title, :body)');
        $stmt->execute([':title' => $title, ':body' => $body]);
        return (int)$pdo->lastInsertId();
    }

    public static function update(int $id, string $title, string $body): bool
    {
        $pdo = getPDO();
        $stmt = $pdo->prepare('UPDATE news SET title = :title, body = :body, updated_at = CURRENT_TIMESTAMP WHERE id = :id');
        return $stmt->execute([':title' => $title, ':body' => $body, ':id' => $id]);
    }

    public static function delete(int $id): bool
    {
        $pdo = getPDO();
        $stmt = $pdo->prepare('DELETE FROM news WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }
}
