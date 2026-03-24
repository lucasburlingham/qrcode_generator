<?php
$config = require __DIR__ . '/config.php';

$code = $_GET['code'] ?? null;
if ($code === null || $code === '') {
    http_response_code(400);
    echo 'Short code is required.';
    exit;
}

if (!preg_match('/^[a-zA-Z0-9]{4,8}$/', $code)) {
    http_response_code(400);
    echo 'Invalid short code.';
    exit;
}

$dbFile = $config['db_path'];
if (!file_exists($dbFile)) {
    http_response_code(404);
    echo 'Not found.';
    exit;
}

$pdo = new PDO('sqlite:' . $dbFile);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$stmt = $pdo->prepare('SELECT url FROM links WHERE code = :code LIMIT 1');
$stmt->execute([':code' => $code]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if ($row === false) {
    http_response_code(404);
    echo 'Link not found.';
    exit;
}

$url = $row['url'];
$scheme = strtolower(parse_url($url, PHP_URL_SCHEME) ?: '');
if (!in_array($scheme, $config['allowed_schemes'], true)) {
    http_response_code(403);
    echo 'Forbidden target URL scheme.';
    exit;
}

$url = str_replace(array("", "
"), '', $url);

header('Location: ' . $url, true, 302);
exit;
