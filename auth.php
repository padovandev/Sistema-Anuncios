<?php
require_once __DIR__ . '/config.php';
header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? $_POST['action'] ?? null;

try {
    if ($method !== 'POST') {
        throw new Exception('Método inválido');
    }

    $pdo = getPDO();

    if ($action === 'register') {
        $name = trim($_POST['name'] ?? '');
        $email = strtolower(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';

        if (!$name || !$email || !$password) throw new Exception('Preencha todos os campos');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) throw new Exception('E-mail inválido');

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('INSERT INTO users (name, email, password) VALUES (:name, :email, :password)');
        $stmt->execute([':name' => $name, ':email' => $email, ':password' => $hash]);

        $_SESSION['user_id'] = (int)$pdo->lastInsertId();
        $_SESSION['user_name'] = $name;
        $_SESSION['user_email'] = $email;

        echo json_encode(['ok' => true]);
        exit;
    }

    if ($action === 'login') {
        $email = strtolower(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';
        if (!$email || !$password) throw new Exception('Informe e-mail e senha');

        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user || !password_verify($password, $user['password'])) {
            throw new Exception('Credenciais inválidas');
        }

        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];

        echo json_encode(['ok' => true]);
        exit;
    }

    if ($action === 'logout') {
        session_unset();
        session_destroy();
        echo json_encode(['ok' => true]);
        exit;
    }

    throw new Exception('Ação inválida');
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
