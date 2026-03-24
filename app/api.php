<?php
header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$base = '/api/links';

function respond($status, $data)
{
	http_response_code($status);
	echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
	exit;
}

function getPDO()
{
	$dbFile = __DIR__ . '/data/links.sqlite';
	if (!is_dir(__DIR__ . '/data'))
	{
		mkdir(__DIR__ . '/data', 0755, true);
	}
	$pdo = new PDO('sqlite:' . $dbFile);
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$pdo->exec('CREATE TABLE IF NOT EXISTS links (code TEXT PRIMARY KEY, url TEXT NOT NULL UNIQUE, created_at TEXT NOT NULL)');
	return $pdo;
}

if (strpos($uri, $base) !== 0)
{
	respond(404, ['error' => 'Not found']);
}

$sub = substr($uri, strlen($base));
$sub = trim($sub, '/');

if ($method === 'GET')
{
	$pdo = getPDO();

	if ($sub === '')
	{
		$stmt = $pdo->query('SELECT code, url, created_at FROM links ORDER BY created_at DESC');
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
		respond(200, ['data' => $rows]);
	}

	if (!preg_match('/^[a-zA-Z0-9]{4}$/', $sub))
	{
		respond(400, ['error' => 'Invalid code']);
	}

	$stmt = $pdo->prepare('SELECT code, url, created_at FROM links WHERE code = :code LIMIT 1');
	$stmt->execute([':code' => $sub]);
	$row = $stmt->fetch(PDO::FETCH_ASSOC);

	if (!$row)
	{
		respond(404, ['error' => 'Not found']);
	}

	respond(200, ['data' => $row]);
}

if ($method === 'POST')
{
	if ($sub !== '')
	{
		respond(404, ['error' => 'Not found']);
	}

	$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
	if (stripos($contentType, 'application/json') === false)
	{
		respond(415, ['error' => 'Expected application/json']);
	}

	$raw = file_get_contents('php://input');
	$payload = json_decode($raw, true);
	if (!is_array($payload) || empty($payload['url']))
	{
		respond(400, ['error' => 'Missing url']);
	}

	$url = trim($payload['url']);
	if (!filter_var($url, FILTER_VALIDATE_URL))
	{
		respond(400, ['error' => 'Invalid URL']);
	}

	$allowedSchemes = ['https', 'ftps', 'sftp', 'ftp', 'mailto', 'ssh', 'ws', 'wss'];
	$scheme = strtolower(parse_url($url, PHP_URL_SCHEME) ?: '');
	if (!in_array($scheme, $allowedSchemes, true))
	{
		respond(400, ['error' => 'Invalid URL scheme. Allowed: https, ftps, sftp, ftp, mailto, ssh, ws, wss']);
	}

	$pdo = getPDO();

	$stmt = $pdo->prepare('SELECT code FROM links WHERE url = :url LIMIT 1');
	$stmt->execute([':url' => $url]);
	$existing = $stmt->fetch(PDO::FETCH_ASSOC);
	if ($existing)
	{
		$code = $existing['code'];
	}
	else
	{
		$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		$code = null;

		for ($i = 0; $i < 8; $i++)
		{
			$candidate = '';
			for ($j = 0; $j < 4; $j++)
			{
				$candidate .= $chars[random_int(0, strlen($chars) - 1)];
			}
			$stmt = $pdo->prepare('SELECT 1 FROM links WHERE code = :code LIMIT 1');
			$stmt->execute([':code' => $candidate]);
			if (!$stmt->fetch())
			{
				$code = $candidate;
				break;
			}
		}

		if (!$code)
		{
			respond(500, ['error' => 'Could not generate short code']);
		}

		$insert = $pdo->prepare('INSERT INTO links (code, url, created_at) VALUES (:code, :url, :created_at)');
		$insert->execute([':code' => $code, ':url' => $url, ':created_at' => date('c')]);
	}

	$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
	$host = $_SERVER['HTTP_HOST'];
	$shortUrl = sprintf('%s://%s/s/%s', $scheme, $host, $code);

	respond(201, ['data' => ['code' => $code, 'url' => $url, 'short_url' => $shortUrl]]);
}

if ($method === 'DELETE')
{
	if ($sub === '')
	{
		respond(400, ['error' => 'Missing code']);
	}

	if (!preg_match('/^[a-zA-Z0-9]{4}$/', $sub))
	{
		respond(400, ['error' => 'Invalid code']);
	}

	$pdo = getPDO();
	$stmt = $pdo->prepare('DELETE FROM links WHERE code = :code');
	$stmt->execute([':code' => $sub]);

	if ($stmt->rowCount() === 0)
	{
		respond(404, ['error' => 'Not found']);
	}

	respond(200, ['data' => ['code' => $sub, 'deleted' => true]]);
}

if (in_array($method, ['PUT', 'PATCH']))
{
	respond(405, ['error' => 'Updates not allowed for shortened links']);
}

respond(405, ['error' => 'Method not allowed']);
