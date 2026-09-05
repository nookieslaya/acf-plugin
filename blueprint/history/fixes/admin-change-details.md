# Fix: Show before-to-after change details

**Type:** Fix
**Status:** verified

## Problem

The Admin Changes table showed that a schema node was modified and its risk
level, but not the meaningful before-to-after property transition.

## Resolution

- Add a `Change details` column to the Admin findings table.
- Render concise transitions for field type, field name, and group title.
- Preserve existing kind, path, severity, rationale, diff, classifier, snapshot,
  and WP-CLI behavior.

## Verification

- PHP lint passed.
- All eleven assertion scripts passed.
- `git diff --check` passed.
- Local rendering confirmed `Field type: image -> number` for the controlled
  field-type-change snapshot pair.
