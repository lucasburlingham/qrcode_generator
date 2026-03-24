<?php
header('Content-Type: application/json; charset=utf-8');

$config = require __DIR__ . '/config.php';

function respond($status, $data)
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_SLASHES);
    exit;
}

function getPDO(array $config)
{
    $dbPath = $config['db_path'];
    $dbDir = dirname($dbPath);

    if (!is_dir($dbDir) && !mkdir($dbDir, 0750, true) && !is_dir($dbDir)) {
        respond(500, ['error' => 'Server error']);
    }

    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('CREATE TABLE IF NOT EXISTS links (code TEXT PRIMARY KEY, url TEXT NOT NULL UNIQUE, created_at TEXT NOT NULL)');

    return $pdo;
}

function allowedScheme(string $url, array $allowed): bool
{
    $scheme = strtolower(parse_url($url, PHP_URL_SCHEME) ?: '');
    return in_array($scheme, $allowed, true);
}

function generateCode(array $config, PDO $pdo): ?string
{
    $charset = $config['code_charset'];
    $length = max(4, (int)$config['code_length']);
    $maxTries = 16;

    for ($i = 0; $i < $maxTries; $i++) {
        $candidate = '';
        for ($j = 0; $j < $length; $j++) {
            $candidate .= $charset[random_int(0, strlen($charset) - 1)];
        }

        $stmt = $pdo->prepare('SELECT 1 FROM links WHERE code = :code LIMIT 1');
        $stmt->execute([':code' => $candidate]);
        if ($stmt->fetch() === false) {
            return $candidate;
        }
    }

    return null;
}

$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
$payload = null;

if (stripos($contentType, 'application/json') !== false) {
    $raw = file_get_contents('php://input');
    $payload = json_decode($raw, true);
} else {
    $payload = $_POST;
}

if (!is_array($payload) || empty($payload['url'])) {
    respond(400, ['error' => 'url is required']);
}

$url = trim($payload['url']);
if (strlen($url) > $config['max_url_length']) {
    respond(400, ['error' => 'url too long']);
}

if (!filter_var($url, FILTER_VALIDATE_URL) || !allowedScheme($url, $config['allowed_schemes'])) {
    respond(400, ['error' => 'invalid url']);
}

try {
    $pdo = getPDO($config);

    $stmt = $pdo->prepare('SELECT code FROM links WHERE url = :url LIMIT 1');
    $stmt->execute([':url' => $url]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing !== false) {
        $code = $existing['code'];
    } else {
        $code = generateCode($config, $pdo);
        if ($code === null) {
            respond(500, ['error' => 'Could not generate short code']);
        }

        $insert = $pdo->prepare('INSERT INTO links (code, url, created_at) VALUES (:code, :url, :created_at)');
        $insert->execute([':code' => $code, ':url' => $url, ':created_at' => date('c')]);
    }

    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $config['canonical_host'] ?: ($_SERVER['HTTP_HOST'] ?? 'localhost');
    $shortUrl = sprintf('%s://%s/s/%s', $protocol, $host, $code);

    respond(201, ['short_url' => $shortUrl, 'code' => $code, 'url' => $url]);
} catch (Exception $e) {
    respond(500, ['error' => 'Internal server error']);
}
