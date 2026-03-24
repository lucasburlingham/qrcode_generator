---
description: Enforce GitHub-flavored Markdown for documentation files
applyTo: '*.md'
---

When editing or creating Markdown files in this repository, use GitHub-flavored Markdown (GFM). Ensure:

- Headings use `#` syntax.
- Code blocks use triple backticks with optional language.
- Lists are properly indented.
- Tables use pipe-delimited rows with header separators.
- Links and images use `[]()` format.

Validate files with a Markdown linter before committing if available. If you are unsure, run `markdownlint` or `prettier --check` on `.md` files.
