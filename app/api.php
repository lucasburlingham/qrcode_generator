<?php
header('Content-Type: application/json; charset=utf-8');

$config = require __DIR__ . '/config.php';

function respond(int $status, array $data)
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
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

function composeShortUrl(array $config, string $code): string
{
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $config['canonical_host'] ?: ($_SERVER['HTTP_HOST'] ?? 'localhost');
    return sprintf('%s://%s/s/%s', $scheme, $host, $code);
}

function getAuthKey(): ?string
{
    $header = $_SERVER['HTTP_X_API_KEY'] ?? '';
    if ($header !== '') {
        return trim($header);
    }
    if (!empty($_GET['api_key'])) {
        return trim($_GET['api_key']);
    }
    return null;
}

function requireApiKey(array $config)
{
    $key = getAuthKey();
    if (empty($config['api_key']) || $key !== $config['api_key']) {
        respond(401, ['error' => 'Unauthorized']);
    }
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

$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$base = '/api/links';

if (strpos($uri, $base) !== 0) {
    respond(404, ['error' => 'Not found']);
}

$sub = trim(substr($uri, strlen($base)), '/');

if ($method === 'GET') {
    $pdo = getPDO($config);

    if ($sub === '') {
        requireApiKey($config);
        $stmt = $pdo->query('SELECT code, url, created_at FROM links ORDER BY created_at DESC');
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        respond(200, ['data' => $rows]);
    }

    if (!preg_match('/^[a-zA-Z0-9]{4,8}$/', $sub)) {
        respond(400, ['error' => 'Invalid code']);
    }

    $stmt = $pdo->prepare('SELECT code, url, created_at FROM links WHERE code = :code LIMIT 1');
    $stmt->execute([':code' => $sub]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row === false) {
        respond(404, ['error' => 'Not found']);
    }

    $row['short_url'] = composeShortUrl($config, $row['code']);
    respond(200, ['data' => $row]);
}

if ($method === 'POST') {
    if ($sub !== '') {
        respond(404, ['error' => 'Not found']);
    }

    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    if (stripos($contentType, 'application/json') === false) {
        respond(415, ['error' => 'Expected application/json']);
    }

    $raw = file_get_contents('php://input');
    $payload = json_decode($raw, true);

    if (!is_array($payload) || empty($payload['url'])) {
        respond(400, ['error' => 'Missing url']);
    }

    $url = trim($payload['url']);
    if (strlen($url) > $config['max_url_length'] || !filter_var($url, FILTER_VALIDATE_URL) || !in_array(strtolower(parse_url($url, PHP_URL_SCHEME) ?: ''), $config['allowed_schemes'], true)) {
        respond(400, ['error' => 'Invalid URL']);
    }

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

    respond(201, ['data' => ['code' => $code, 'url' => $url, 'short_url' => composeShortUrl($config, $code)]]);
}

if ($method === 'DELETE') {
    requireApiKey($config);

    if ($sub === '') {
        respond(400, ['error' => 'Missing code']);
    }

    if (!preg_match('/^[a-zA-Z0-9]{4,8}$/', $sub)) {
        respond(400, ['error' => 'Invalid code']);
    }

    $pdo = getPDO($config);
    $stmt = $pdo->prepare('DELETE FROM links WHERE code = :code');
    $stmt->execute([':code' => $sub]);

    if ($stmt->rowCount() === 0) {
        respond(404, ['error' => 'Not found']);
    }

    respond(200, ['data' => ['code' => $sub, 'deleted' => true]]);
}

respond(405, ['error' => 'Method not allowed']);
