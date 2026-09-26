# Feature: Pro migration review and rollback workspace

**From build-plan:** feature 29c
**Status:** verified

## Goal

Provide a value-free Admin history of controlled direct migrations and a guarded rollback that removes only exact target meta rows recorded by the corresponding execution journal.

## In scope

- List recent migration executions with status and safe counters.
- Show one execution's identifier-only journal rows.
- Download a JSON report without field values.
- Roll back only journaled target value/reference rows after a nonce, Pro-capability, and `manage_options` check.
- Preserve old keys and never delete a target row unless both its exact meta ID and expected meta key match the journal.
- Tests, English/Polish strings, and a local runtime check.

## Out of scope

- Rollback by field name, nested data, batch automation, restoring deleted source data, or displaying values.

## Build steps

- [x] **Step 1 - history and guarded rollback service** - add query methods, exact-row validation, execution terminal state, and tests. *Done when:* unknown or stale rows are skipped and cannot be removed.
- [x] **Step 2 - Admin history, report, and rollback action** - add a history view and secure controls. *Done when:* only Pro administrators can download or roll back a completed execution.
- [x] **Step 3 - translations, Local evidence, and final checks** - document the boundary and prove rollback removes only journaled rows. *Done when:* tests, syntax checks, translations, and Local evidence pass.

## Testing

- `sh wp-content/plugins/acf-schema-guard/tests/run-assertions.sh`, PHP lint, `msgfmt --check`, and Local WordPress fixture evidence.

## Notes for the AI

- Fail closed. Journal metadata is authoritative, never a browser-supplied meta ID.
- Do not alter the user's unrelated Hero JSON change.
