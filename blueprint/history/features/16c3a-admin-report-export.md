# Feature: Admin report export

**From build-plan:** feature 16c3a
**Status:** verified

## Delivered

- Pro-only release report download from Changes.
- Markdown and JSON format selection, protected by capability, administrator
  permission, and nonce checks.
- A responsive ACF Schema Guard styled export card.
- Markdown findings with severity summary, details, paths, and rationale.

## Verification

- `sh wp-content/plugins/acf-schema-guard/tests/run-assertions.sh` passed.
