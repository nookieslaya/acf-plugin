# Fix: Add repeatable nested-impact test fixtures

**Type:** Fix
**Status:** verified

## The problem

Direct and nested data-impact states needed a repeatable visible manual test.
Existing theme field groups contain user-authored changes, so altering them would
make results ambiguous.

## The fix

Added a separate inactive development fixture group and a concise manual guide
for a direct field, Group child, Repeater child, Flexible Content child, and
nested Flexible Content plus Repeater child. The unknown-coverage state remains
covered in automated rendering assertions because ACF Clone storage cannot be
fabricated portably.

## Verification

- `jq empty wp-content/themes/acf-schema-guard-dev/acf-json/group_acf_schema_guard_impact_fixture.json`
- `php wp-content/plugins/acf-schema-guard/tests/nested-impact-fixture-assertions.php`
- `sh wp-content/plugins/acf-schema-guard/tests/run-assertions.sh`

## Constraints

- The user-authored Hero and Card Local JSON files were not changed.
- Fixture values are entered only into temporary test pages and are never read or
  displayed by the plugin.
- Fixture keys begin with `acf_schema_guard_impact_fixture_`.
