<?php
header('Content-Type: application/json; charset=utf-8');

$dbFile = __DIR__ . '/data/links.sqlite';
if (!is_dir(__DIR__ . '/data'))
{
	mkdir(__DIR__ . '/data', 0755, true);
}

$pdo = new PDO('sqlite:' . $dbFile);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->exec('CREATE TABLE IF NOT EXISTS links (code TEXT PRIMARY KEY, url TEXT NOT NULL UNIQUE, created_at TEXT NOT NULL)');

$payload = null;
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
if (stripos($contentType, 'application/json') !== false)
{
	$raw = file_get_contents('php://input');
	$payload = json_decode($raw, true);
}
else
{
	$payload = $_POST;
}

if (empty($payload['url']))
{
	http_response_code(400);
	echo json_encode(['error' => 'URL is required']);
	exit;
}

$url = trim($payload['url']);
if (!filter_var($url, FILTER_VALIDATE_URL))
{
	http_response_code(400);
	echo json_encode(['error' => 'Invalid URL format']);
	exit;
}

$allowedSchemes = ['https', 'ftps', 'sftp', 'ftp', 'mailto', 'ssh'];
$scheme = strtolower(parse_url($url, PHP_URL_SCHEME) ?: '');
if (!in_array($scheme, $allowedSchemes, true))
{
	http_response_code(400);
	echo json_encode(['error' => 'Invalid URL scheme. Allowed: https, ftps, sftp, ftp, mailto, ssh, ws, wss']);
	exit;
}

if (strlen($url) > 2048)
{
	http_response_code(400);
	echo json_encode(['error' => 'URL too long']);
	exit;
}

try
{
	// check for existing URL
	$stmt = $pdo->prepare('SELECT code FROM links WHERE url = :url LIMIT 1');
	$stmt->execute([':url' => $url]);
	$row = $stmt->fetch(PDO::FETCH_ASSOC);
	if ($row)
	{
		$code = $row['code'];
	}
	else
	{
		$characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		$maxTry = 8;

		$code = null;
		for ($i = 0; $i < $maxTry; $i++)
		{
			$candidate = '';
			for ($j = 0; $j < 4; $j++)
			{
				$candidate .= $characters[random_int(0, strlen($characters) - 1)];
			}
			$stmt = $pdo->prepare('SELECT 1 FROM links WHERE code = :code LIMIT 1');
			$stmt->execute([':code' => $candidate]);
			if (!$stmt->fetch())
			{
				$code = $candidate;
				break;
			}
		}

		if (empty($code))
		{
			throw new RuntimeException('Could not generate a unique short code. Try again.');
		}

		$insert = $pdo->prepare('INSERT INTO links (code, url, created_at) VALUES (:code, :url, :created_at)');
		$insert->execute([':code' => $code, ':url' => $url, ':created_at' => date('c')]);
	}

	$host = $_SERVER['HTTP_HOST'];
	$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
	$shortUrl = sprintf('%s://%s/s/%s', $scheme, $host, $code);

	echo json_encode(['short_url' => $shortUrl, 'code' => $code, 'url' => $url]);
	exit;
}
catch (Exception $e)
{
	http_response_code(500);
	echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
	exit;
}
