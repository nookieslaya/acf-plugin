# Feature: Nested impact presentation

**From build-plan:** feature 20b
**Status:** verified

## Goal

Make direct, nested, and unknown stored-data evidence understandable in Changes,
without treating a zero result as proof that no affected data exists.

## Delivered

- Added clear text-labelled evidence states: Direct storage, Nested ACF storage,
  and Coverage unknown.
- Shows a human-readable field path and storage pattern for schema-derived nested
  evidence, never the internal regular expression used by the database query.
- Preserves legacy impact payloads as direct evidence.
- Explains that identifiers are review evidence only, values are not read, and
  unsupported storage structures are intentionally not queried.
- Added responsive card layout and rendering assertions for all three states.

## Verification

- `php -l wp-content/plugins/acf-schema-guard/includes/admin/class-admin-controller.php`
- `php wp-content/plugins/acf-schema-guard/tests/admin-snapshot-query-assertions.php`
- `sh wp-content/plugins/acf-schema-guard/tests/run-assertions.sh`
- Manual WordPress Admin review confirmed the three evidence states in Changes.

## Follow-up

Feature 21 will identify potentially unused ACF fields as review signals only.
