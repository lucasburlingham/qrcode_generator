---
name: SPECS
description: Always use this prompt. 
---

# QR Code Generator SPECS

## Overview

A browser-based QR code generator in a single `index.html` file. Features input-based generation, live preview, download in PNG/JPG/SVG, padding and drop-shadow shader, and URL parameter support. The specification defines the requirements, features, behaviors, API contract, accessibility considerations, testing steps, and change log for the project. You must always follow these specifications when making changes to the QR code generator. 

## Requirements

- Browser with canvas support
- Internet for `qrcode` CDN library
- Optional local server if `file://` restrictions apply

## Feature Matrix

- Input text/URL
- Size selector
- Foreground/background color pickers (inline color swatches)
- Padding (px)
- Optional drop shadow behind QR block
- Live update while editing
- Download buttons: PNG, JPG, SVG
- URL query params to configure state and optionally auto-download
  - `text` (string)
  - `size` (number)
  - `fg` (hex color, e.g., `%23000000`)
  - `bg` (hex color)
  - `padding` (number)
  - `shadow` (`1|0|true|false`)
  - `filetype` (`png|jpg|jpeg|svg`)
  - `download` (`1|0`, default download enabled when filetype is present)

## Behaviors

- If no text: canvas clears and no output.
- `renderQRCode()` draws a shadowed white box (optional) then QR code.
- `downloadSVG()` uses stored last SVG string; if not available, shows alert.
- Auto-download is executed with a 250ms delay after page load.

## API Contract (URL Parameters)

```curl
/index.html?text=<text>&size=<px>&fg=<hex>&bg=<hex>&padding=<px>&shadow=<0|1>&filetype=<png|jpg|svg>&download=<0|1>
```

## Accessibility

- All inputs have label references.
- UI is keyboard accessible.

## Testing

1. Open `index.html`, verify QR updates on input change.
2. Verify export buttons produce correct file.
3. Confirm URL param feature: open with `?text=hello&filetype=png` and file downloads.
4. Confirm `shadow` toggle and `padding` slider impact rendering.