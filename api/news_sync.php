<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json; charset=utf-8');

try {
    require_login();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método inválido');
    }

    $year = (int)($_POST['year'] ?? 0);
    $month = (int)($_POST['month'] ?? 0);

    if ($year < 1900 || $month < 1 || $month > 12) {
        throw new Exception('Ano ou mês inválido');
    }

    $pdo = getPDO();

    // Verifica se já sincronizou esse mês/ano
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM news 
        WHERE source = 'NYTimes' 
          AND strftime('%Y', created_at) = :y 
          AND strftime('%m', created_at) = :m
    ");
    $stmt->execute([
        ':y' => (string)$year,
        ':m' => str_pad((string)$month, 2, '0', STR_PAD_LEFT)
    ]);

    if ((int)$stmt->fetchColumn() > 0) {
        echo json_encode(['ok' => false, 'error' => 'Este mês/ano já foi sincronizado.']);
        exit;
    }

    // Monta URL da API do NYTimes
    $url = sprintf(
        'https://api.nytimes.com/svc/archive/v1/%d/%d.json?api-key=%s',
        $year,
        $month,
        urlencode(NYT_API_KEY)
    );

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 60,
    ]);
    $resp = curl_exec($ch);
    if ($resp === false) {
        throw new Exception('Falha ao acessar API: ' . curl_error($ch));
    }
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($code !== 200) {
        throw new Exception('API retornou status ' . $code);
    }

    $json = json_decode($resp, true);
    $docs = $json['response']['docs'] ?? [];
    if (!$docs) throw new Exception('Sem documentos para importar.');

    $insert = $pdo->prepare("
        INSERT INTO news (title, body, created_at, updated_at, source) 
        VALUES (:t, :b, :c, :u, 'NYTimes')
    ");
    $count = 0;

    foreach ($docs as $d) {
        $title = $d['headline']['main'] ?? '';
        if (!$title) continue;
        $body = $d['abstract'] ?? ($d['snippet'] ?? '');
        $pub  = $d['pub_date'] ?? date('Y-m-d H:i:s');
        $ok = $insert->execute([
            ':t' => $title,
            ':b' => $body,
            ':c' => $pub,
            ':u' => $pub,
        ]);
        if ($ok) $count++;
    }

    echo json_encode(['ok' => true, 'imported' => $count]);

} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}