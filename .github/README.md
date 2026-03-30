# QR Code Generator

A lightweight single-page QR code generator. This project is designed for fast local usage, with controls for text, size, colors, padding, and drop shadow, plus PNG/JPG/SVG exports.

## Features

- Live QR preview as you type
- Foreground and background color selection
- Padding controls
- Drop shadow option
- Download as PNG, JPG, or SVG
- URL-based auto-configuration and export

## Usage

1. Open `index.html` in your browser.
2. Enter text/URL in the provided textarea.
3. Adjust size (px), colors, padding, and shadow.
4. QR updates automatically.
5. Click Download button for target format.

## URL Query Parameters

Set values via query string (works on load):

- `text` (e.g., `hello`)
- `size` (e.g., `320`)
- `fg` (hex color, URL-encoded, e.g. `%230f172a`)
- `bg` (hex color)
- `padding` (px)
- `shadow` (`1|0|true|false`)
- `filetype` (`png`, `jpg`, `jpeg`, `svg`)
- `download` (`1` or `0`)

Example:

```curl
index.html?text=https%3A%2F%2Fexample.com&size=480&fg=%23000000&bg=%23ffffff&padding=12&shadow=1&filetype=png
```

## Notes

- SVG download is generated from a separate SVG string to preserve vector quality.
- Drop shadow is drawn behind the white QR block only in raster outputs.

## Development

No build tools required. Edit `index.html` directly.

### URL shortener backend

This project now includes an integrated link shortener with 4-character codes, backed by SQLite. App code lives in `app/`.

Endpoints:

- `POST /shorten.php` (legacy) or `POST /api/links` with JSON `{ "url": "https://example.com" }` returns `{ "data": { "code": "Ab1c", "url": "https://example.com", "short_url": "http://<host>/s/Ab1c" }}`.
  Allowed URI schemes: `https`, `ftps`, `sftp`, `ftp`, `mailto`, `ssh`.
- `GET /api/links` returns all shortened links.
- `GET /api/links/<code>` returns metadata for a single short code.
- `GET /s/<code>` redirects to original URL.

Updates are disallowed: `PUT`, `PATCH`, `DELETE` return `405 Method not allowed`.

### Docker support

A Docker configuration has been added for containerized hosting of this app with PHP-FPM + Nginx.

- `Dockerfile` uses `php:8.2-fpm-alpine`, installs `nginx`, and copies `app/` into `/var/www/html`.
- `docker-compose.yml` maps host `8080` to container `80`, uses a named volume `data` for SQLite storage.
- `.dockerignore` excludes common unwanted files and local output directories.

App files are now in `app/`:

- `app/index.html` (UI)
- `app/nginx.conf` (routing)
- `app/shorten.php` (legacy shortener endpoint)
- `app/redirect.php` (short URL redirect)
- `app/api.php` (REST API for link management)

A companion skill metadata file has been added at `.github/skills/update-build-push-docker.SKILL.md` to document the update/build-push workflow and keep repo operations reproducible.

Markdown style note: Add blank lines before and after headings and list blocks in `.md` files for consistent GFM rendering.

Run locally:

```bash
docker compose up --build -d
```

Open in browser:

- `http://localhost:8080`

API usage examples:

```bash
curl -X POST -H "Content-Type: application/json" -d '{"url":"https://example.com"}' http://localhost:8080/api/links
curl http://localhost:8080/api/links
curl http://localhost:8080/api/links/<code>
```

Short URL redirect:

- `http://localhost:8080/s/<code>`

Push image to Docker Hub (replace `<yourhub>`):

```bash
docker login
docker build -t qrcode_generator:latest .
docker tag qrcode_generator:latest <yourhub>/qrcode_generator:latest
docker push <yourhub>/qrcode_generator:latest
```

## License

The Creator's License (lucasburlingham.me/license.html) - free for personal and commercial use with attribution. See license for details.
