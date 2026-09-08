# Feature: Changes-to-code integration

**From build-plan:** feature 14c
**Status:** verified

## Delivered

- The Changes screen scans the active theme once for the current comparison.
- Each changed field can show its matching literal PHP ACF references directly below the schema finding.
- Affected references include impact severity, path, line, and expression.
- Field-group changes and fields with no literal reference preserve the existing Changes view.

## Verification

- `php -l wp-content/plugins/acf-schema-guard/includes/admin/class-admin-controller.php`
- `php -l wp-content/plugins/acf-schema-guard/includes/class-plugin.php`
- `sh wp-content/plugins/acf-schema-guard/tests/run-assertions.sh`

## Manual check

Open **ACF Schema Guard -> Changes** with a newer snapshot that changes a field used in the active theme. Confirm that **Affected code references** appears under that field and contains the expected path, line, expression, and severity.
