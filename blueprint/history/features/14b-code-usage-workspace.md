# Feature: Code Usage workspace

**From build-plan:** feature 14b
**Status:** verified

## Goal

Provide a practical, readable WordPress Admin workspace for locating literal PHP ACF field references in the active theme.

## Delivered

- Groups references under their ACF field name so legitimate repeated uses are easy to understand.
- Shows file path, line, supported ACF expression, and an expandable nearby source preview.
- Restricts source previews to the active theme root.
- Filters results by field, file, and supported ACF function with validated request values.
- Provides responsive controls, expandable result cards, and an empty state.

## Verification

- `php -l wp-content/plugins/acf-schema-guard/includes/admin/class-admin-controller.php`
- `sh wp-content/plugins/acf-schema-guard/tests/run-assertions.sh`
- The Admin assertion renders the Code Usage page against the real development theme and proves that `card_title`, its file path and expression render, and that the field filter excludes unrelated result cards.

## Manual check

Open **ACF Schema Guard -> Code Usage**, expand a field, then expand a location. Confirm that the displayed line and code preview match the theme. Use the Field, File, and ACF function controls to narrow the list.
