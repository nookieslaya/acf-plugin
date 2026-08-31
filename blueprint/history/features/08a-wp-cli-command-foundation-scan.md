# Feature: WP-CLI command foundation and scan

**From build-plan:** feature 8a
**Status:** verified

## Goal

Expose the existing PHP ACF usage scanner through a safe, optional WP-CLI
command. Developers can scan explicit source directories locally or in CI
without making the web or WordPress Admin runtime depend on WP-CLI.

## In scope

- Register `wp acf-schema-guard scan` only when WP-CLI is active.
- Accept and validate one or more explicit source-root directories.
- Scan through `CodeUsageScannerService` and `PhpAcfUsageScanner`.
- Provide deterministic table output or `--format=json`.
- Document the read-only command and add isolated WP-CLI contract assertions.

## Out of scope

- Snapshot capture, diff, risk analysis, `check`, or `--fail-on-breaking`.
- Automatic root discovery, saved scanner settings, non-PHP scanners, dynamic
  expressions, CI configuration, or web/Admin runtime dependencies.

## Build steps

- [x] **Step 1 - Optional CLI registration boundary** - added a CLI-only
  registrar and guarded its loading behind WP-CLI availability.
- [x] **Step 2 - Read-only scan command** - added directory validation, scanner
  composition, and table or JSON output for `scan <source-root>...`.
- [x] **Step 3 - CLI evidence and documentation** - added a local WP-CLI test
  double, plugin assertions, lint evidence, and README usage documentation.

## Files / areas

- `includes/cli/class-command-registrar.php` - WP-CLI registration boundary.
- `includes/cli/class-scan-command.php` - scan validation, composition, output.
- `includes/class-plugin.php` - CLI-only command registration.
- `tests/wp-cli-scan-assertions.php` - isolated command contract assertions.
- `readme.md` - command syntax and limitations.

## Data / contracts

- Syntax: `wp acf-schema-guard scan <source-root>... [--format=<format>]`.
- Roots must resolve to readable directories and are never modified or executed.
- Supported formats: `table` and `json`; fields are `field_name`, `strategy`,
  `path`, `line`, and `expression`.
- Empty valid scans succeed; invalid roots and formats use WP-CLI errors.
- The command writes no WordPress data, files, snapshots, or configuration.

## Testing

- PHP lint passed for the command registrar, scan command, plugin composition
  root, and new assertion script.
- `php tests/wp-cli-scan-assertions.php` passed.
- All nine plugin assertion scripts passed.
- No live WP-CLI database run was required; the local environment has not yet
  established reliable WP-CLI database connectivity.
