# Fix: Breaking rename classification and token-aware PHP scanning

**Type:** Fix
**Status:** verified
**Fixes:** F-01, F-02

## The problem

Two confirmed P1 defects weakened the plugin's main safety promises:

- A field-name change was classified as `warning`, allowing both snapshot and
  Git baseline checks to succeed with `--fail-on-breaking` even when existing
  `get_field()` calls target the old name.
- The PHP usage scanner applied a regular expression to raw source text, so
  comments, quoted examples, object methods, and other non-executable text
  could be reported as live ACF calls.

## The fix

- A modified field `name` is `high` with the rationale `Field name changed.`.
  Additions remain `safe`, removals `critical`, type changes `high`, and other
  modifications `warning`.
- PHP source is scanned with `token_get_all()`. The scanner accepts supported
  unqualified or explicitly global calls with a literal first argument and
  rejects comments, strings, declarations, method/static calls, qualified
  non-global calls, dynamic arguments, and malformed candidates.
- Public scanner output remains field name, `php-acf` strategy, path, 1-based
  line, expression, deterministic aggregation, and read-only behavior.

## Verification

- `php -l` passed for all plugin and development-theme PHP files.
- All 16 `tests/*assertion*.php` scripts passed.
- Focused assertions cover the rename policy in snapshot analysis, stored
  snapshot CLI checks, and Git baseline CLI checks.
- Focused scanner and CLI fixtures cover every supported function plus the
  rejected non-executable and dynamic forms.
- A repair audit re-reviewed both P1 findings and closed them.

## Findings

### breaking-rename-classification-and-token-aware-php-scanning/F-01 [P1] closed - Field-name changes do not fail the breaking-change check

**File:** wp-content/plugins/acf-schema-guard/includes/diff/class-risk-classifier.php:4
**Found:** 2026-09-06 by /audit (scope: full; lens: quality, security, performance, tests)
**Why it matters:** A field name is the lookup contract used by `get_field()` and related APIs. A rename can break templates while CI exits successfully when it is classified below the breaking threshold.
**Suggested fix:** Classify field-name changes as at least `high`, give them a specific rationale, and add classifier plus WP-CLI check regression coverage for a rename.
**Resolution:** Re-reviewed on 2026-09-06. The classifier assigns `high` with `Field name changed.` to a same-key field rename, while preserving the existing addition, removal, type-change, and generic-modification policies. Focused snapshot, stored-snapshot CLI, and Git-baseline CLI assertions all pass with `--fail-on-breaking` for the rename case. Closed by the repair audit.

### breaking-rename-classification-and-token-aware-php-scanning/F-02 [P1] closed - PHP scanner reports comments and strings as live ACF calls

**File:** wp-content/plugins/acf-schema-guard/includes/scanner/class-php-acf-usage-scanner.php:7
**Found:** 2026-09-06 by /audit (scope: full; lens: quality, security, performance, tests)
**Why it matters:** Raw source matching can report ACF calls that do not execute, sending developers to call sites that do not exist.
**Suggested fix:** Tokenize PHP with `token_get_all()`, inspect executable function-call tokens and literal first arguments, and add positive and negative fixtures.
**Resolution:** Re-reviewed on 2026-09-06. The scanner walks `token_get_all()` output and accepts only supported unqualified or explicitly global function calls with one literal first argument. Focused scanner and WP-CLI fixtures confirm that comments, strings, declarations, object/static methods, namespace-qualified calls, and dynamic or concatenated arguments yield no reference. Closed by the repair audit.
