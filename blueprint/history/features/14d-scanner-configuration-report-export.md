# Feature: Scanner configuration and report export

**From build-plan:** feature 14d
**Status:** verified

- Safe scanner roots are limited to WordPress themes and plugins.
- Settings saves configured roots for Admin analysis.
- `wp acf-schema-guard report export <path> --format=json|markdown` exports code usage.
- Existing files require `--force` before replacement.
- Full assertion runner passed, including export and overwrite protection.
