<?php
$code = $_GET['code'] ?? null;
if (!$code) {
    http_response_code(400);
    echo 'Short code is required.';
    exit;
}

if (!preg_match('/^[a-zA-Z0-9]{4}$/', $code)) {
    http_response_code(400);
    echo 'Invalid short code.';
    exit;
}

$dbFile = __DIR__ . '/data/links.sqlite';
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

if (!$row) {
    http_response_code(404);
    echo 'Link not found.';
    exit;
}

$url = $row['url'];
header('Location: ' . $url, true, 302);
exit;
