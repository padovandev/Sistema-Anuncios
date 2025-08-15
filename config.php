<?php
// Inicialização de sessão e configuração de banco (SQLite)
session_start();

const DB_PATH = __DIR__ . '/database.sqlite';
const NYT_API_KEY = 'pLs3AITT5xJy8bQ8D6dVyFmV5ETZ6CzK'; // <- sua chave NYTimes

function getPDO(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO('sqlite:' . DB_PATH);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        init_db($pdo);
    }
    return $pdo;
}

function init_db(PDO $pdo): void
{
    // Cria de tabelas se não existirem
    $pdo->exec('CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        email TEXT NOT NULL UNIQUE,
        password TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )');

    $pdo->exec('CREATE TABLE IF NOT EXISTS news (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT NOT NULL,
        body TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        source TEXT DEFAULT "local"
    )');

    // Se a coluna source não existir em bancos antigos, tenta adicionar
    try {
        $cols = $pdo->query("PRAGMA table_info(news)")->fetchAll(PDO::FETCH_ASSOC);
        $hasSource = false;
        foreach ($cols as $c) {
            if (strtolower($c['name']) === 'source') {
                $hasSource = true;
                break;
            }
        }
        if (!$hasSource) {
            $pdo->exec('ALTER TABLE news ADD COLUMN source TEXT DEFAULT "local"');
        }
    } catch (Throwable $e) { /* ignora */
    }
}

function require_login(): void
{
    if (empty($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['ok' => false, 'error' => 'Não autenticado']);
        exit;
    }
}
