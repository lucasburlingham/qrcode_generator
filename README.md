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

## License

The Creator's License (lucasburlingham.me/license.html) - free for personal and commercial use with attribution. See license for details.