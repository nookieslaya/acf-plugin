# Feature: Unused-field inventory

**From build-plan:** feature 21a
**Status:** verified

## Delivered

- Deterministic review-only coverage for current normalized ACF fields.
- Literal PHP reference counts and nested-field traversal.
- Explicit dynamic-call uncertainty and configured-scanner-root limitation.
- No automatic deletion, migration, or claim that a field is safe to remove.

## Verification

- `sh wp-content/plugins/acf-schema-guard/tests/run-assertions.sh` passed.
