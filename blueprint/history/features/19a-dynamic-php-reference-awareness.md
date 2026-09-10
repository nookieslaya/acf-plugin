# Feature: Dynamic PHP reference awareness

**From build-plan:** feature 19a
**Status:** verified

## Goal

Expose supported PHP ACF calls whose field argument is dynamic as explicit
manual-review evidence, without guessing the field name or attaching the call to
a schema finding.

## Delivered

- Added a separate immutable dynamic-reference model with source location,
  expression, scanner strategy, and `manual_review_required` status.
- Extended the token-based PHP scanner for non-literal arguments in supported
  global ACF calls while preserving literal-reference results and exclusions for
  declarations, methods, and namespaced calls.
- Added a clearly labelled manual-review notice to Code Usage and Changes.
- Included dynamic evidence in JSON and Markdown report export.

## Data / contracts

Dynamic references never include `field_name`, severity, or a guessed link to a
schema change. Existing literal references retain their existing output fields.

## Verification

- `sh wp-content/plugins/acf-schema-guard/tests/run-assertions.sh`
- PHP lint for changed scanner, CLI, and Admin files.
- Manual Admin confirmation of the dynamic-call notice in Code Usage.

## Follow-up

Feature 19b will provide a bounded, read-only stored-data impact inventory for
removed and renamed field names.
