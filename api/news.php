<?php
require_once __DIR__ . '/../News.php';
header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? $_POST['action'] ?? null;

try {
    // Todas as rotas de notícias exigem usuário logado
    require_login();

    if ($method === 'GET') {
        if ($action === 'list') {
            // Filtros opcionais: título e mês (Exercício 2)
            $pdo = getPDO();
            $title = '%' . trim($_GET['title'] ?? '') . '%';
            $month = trim($_GET['month'] ?? ''); // formato 01..12
            $sql = 'SELECT * FROM news WHERE title LIKE :title';
            $params = [':title' => $title];
            if ($month !== '') {
                $sql .= " AND strftime('%m', created_at) = :m";
                $params[':m'] = $month;
            }
            $sql .= ' ORDER BY id DESC';
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            echo json_encode(['ok' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
            exit;
        }
        if ($action === 'get') {
            $id = (int)($_GET['id'] ?? 0);
            if (!$id) throw new Exception('ID inválido');
            $n = News::find($id);
            if (!$n) throw new Exception('Notícia não encontrada');
            echo json_encode(['ok' => true, 'data' => $n]);
            exit;
        }
    }

    if ($method === 'POST') {
        if ($action === 'create') {
            $title = trim($_POST['title'] ?? '');
            $body = trim($_POST['body'] ?? '');
            if (!$title || !$body) throw new Exception('Título e conteúdo são obrigatórios');
            $id = News::create($title, $body);
            echo json_encode(['ok' => true, 'id' => $id]);
            exit;
        }
        if ($action === 'update') {
            $id = (int)($_POST['id'] ?? 0);
            $title = trim($_POST['title'] ?? '');
            $body = trim($_POST['body'] ?? '');
            if (!$id || !$title || !$body) throw new Exception('Dados inválidos');
            $ok = News::update($id, $title, $body);
            echo json_encode(['ok' => $ok]);
            exit;
        }
        if ($action === 'delete') {
            $id = (int)($_POST['id'] ?? 0);
            if (!$id) throw new Exception('ID inválido');
            $ok = News::delete($id);
            echo json_encode(['ok' => $ok]);
            exit;
        }
    }

    throw new Exception('Rota ou método inválido');
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
