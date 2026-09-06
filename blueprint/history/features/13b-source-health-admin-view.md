# Feature: Source health Admin view

**From build-plan:** feature 13b
**Status:** verified

## Goal

Show the current ACF source-health result in the existing Field Groups screen so an administrator can identify groups that are aligned with `acf-json` or require review.

## Completed work

- Connected the read-only `source_health()` callback to `AdminController`.
- Replaced the Field Groups placeholder with available, unavailable, empty, and result states.
- Added field-group key, status, database presence, Local JSON presence, and recommended-action output.
- Added text labels and page-scoped status styling.
- Added isolated assertion coverage for rendered divergent guidance.

## Verification

- `php -l` passed for changed PHP files.
- `sh wp-content/plugins/acf-schema-guard/tests/run-assertions.sh` passed.
- Manual check confirmed the Field Groups table is visible in WordPress Admin.

## Notes

- The view is diagnostic only. It never imports, synchronizes, edits, or deletes ACF or JSON data.
