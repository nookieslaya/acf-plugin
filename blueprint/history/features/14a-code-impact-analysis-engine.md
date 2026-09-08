# Feature: Code-impact analysis engine

**From build-plan:** feature 14a
**Status:** verified

## Goal

Connect classified ACF schema changes with PHP ACF usage references so later Admin and CI views can show exactly which files and lines require review.

## Delivered

- Immutable impact records link one schema change to one PHP reference.
- Removed fields classify as `critical`, field-name changes as `high`, and type changes as `warning`.
- Duplicate scanner references produce one deterministic impact record.
- `Plugin::analyze_code_impact()` exposes the analyzer for Admin and CLI consumers.

## Verification

- `php -l wp-content/plugins/acf-schema-guard/includes/impact/class-code-impact.php`
- `php -l wp-content/plugins/acf-schema-guard/includes/impact/class-code-impact-analyzer.php`
- `php -l wp-content/plugins/acf-schema-guard/includes/class-plugin.php`
- `sh wp-content/plugins/acf-schema-guard/tests/run-assertions.sh`

## Notes

- A reference proves a supported literal PHP call site, not runtime reachability.
