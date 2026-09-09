# Feature: Live baseline comparison

**From build-plan:** feature 17a
**Status:** verified

## Goal

Compare the approved stored baseline with the effective ACF schema loaded for
the current request, without capturing a separate current snapshot.

## Completed work

- Added a read-only live-analysis contract built on the existing diff and risk
  classification services.
- Added `Plugin::analyze_live_baseline()` with safe unavailable states for a
  missing baseline or unavailable ACF runtime.
- Updated Changes to render the stored baseline against the current live schema
  and retain existing change explanations and code impacts.
- Documented the no-capture Admin workflow.

## Verification

- `sh wp-content/plugins/acf-schema-guard/tests/run-assertions.sh` passed.
- A real Local WP-CLI invocation returned `available: true`, a valid baseline
  ID, and an empty findings list for a matching live schema.

## Manual check

Set a baseline, edit an ACF field type, and refresh **ACF Schema Guard →
Changes** without capturing a current snapshot. The live change must appear
with its normal severity and code references.
