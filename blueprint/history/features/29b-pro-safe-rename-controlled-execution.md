# Feature: Pro safe rename controlled execution

**From build-plan:** feature 29b
**Status:** verified

## Goal

Let an administrator execute a reviewed Pro plan for a direct ACF field-name
rename, but only for explicitly selected, still-eligible records. The action
copies data to the new ACF key, retains every old key, writes a value-free audit
journal, and never performs a blind or bulk migration.

## In scope

- Extend a reviewed migration plan with the immutable ACF field key required to
  write the companion `_new_field_name` reference safely.
- Add plugin-owned execution and backup-journal tables. The journal records
  identifiers, key names, timestamps, users, and hashes only. It never stores a
  second copy of a field value.
- Re-query direct post-meta candidates at execution time and show a bounded
  selection list for one reviewed plan.
- Permit only an explicitly selected maximum of 20 candidates per execution.
- Copy exact direct values and their ACF reference metadata to the new keys;
  retain the old value and old reference keys unchanged.
- Re-check plan fingerprint, current schema hash, plan review status, source
  presence, and target-key conflicts immediately before every write.
- Skip conflicts and unavailable records with an auditable reason; a failure on
  one record must not modify that record partially or silently migrate another.
- Add an administrator-only, nonce-protected execution action and concise Admin
  result state beside the existing Pro migration plan.
- Add focused tests, English and Polish strings, and documentation.

## Out of scope

- Nested ACF fields, options, users, terms, comments, custom tables, dynamic
  calls, scheduled jobs, CLI execution, REST endpoints, or unbounded batches.
- Replacing or deleting old meta keys, changing ACF definitions, or resolving a
  target-key conflict automatically.
- Reading or displaying field values in Admin, reports, plans, or backups.
- Admin execution history, downloadable execution reports, and user-triggered
  rollback. Feature 29c owns that workspace and any reversal control.

## Build loop

Build each step on a feature branch. The repository assertion runner must pass
after each logic step. A Local WordPress test uses disposable records and
verifies the old keys remain present, the new keys receive matching values, and
the audit journal has no value column.

## Build steps

- [x] **Step 1 - execution and backup-journal contracts** - extend the plan
  contract with the direct ACF field key, add execution status/value objects and
  plugin tables for identifier-only execution and written-meta journal rows.
  *Done when:* table schemas contain no value payload, a backup row identifies
  only values written by an execution, and invalid state transitions fail.
- [x] **Step 2 - bounded direct-meta executor** - implement a repository and
  service that revalidates a reviewed direct rename, accepts only selected live
  candidates, writes new value plus ACF reference keys atomically per record,
  preserves old keys, and journals every success, conflict, skip, or failure.
  *Done when:* exactly selected conflict-free records are copied, old keys stay
  unchanged, stale plans and unsupported inputs make no writes, and each write
  has a value-free journal entry.
- [x] **Step 3 - secure Changes execution control** - show at most 20 fresh
  eligible records with explicit checkboxes and an execution confirmation,
  provide a capability, nonce, and `manage_options` guarded post action, then
  show a count-only result without exposing values.
  *Done when:* Free users and invalid requests cannot execute; unchecked,
  stale, conflicting, or nested records cannot be selected or migrated; the
  successful local flow visibly retains a plan and reports copied/skipped counts.
- [x] **Step 4 - documentation, localisation, and evidence** - document the
  exact direct-only migration semantics, conflict policy, retained old keys,
  batch limit, and feature-29c rollback boundary; translate all copy and add
  tests for data safety and Admin access contracts.
  *Done when:* assertions, syntax checks, translation validation, and a Local
  WordPress fixture prove the before/after meta-key behavior without rendering
  or persisting any field value outside WordPress post meta.

## Files / areas

- `includes/migrations/` - plan extension, execution model, journal tables,
  repositories, validation, and direct-meta executor.
- `includes/class-plugin.php` and plugin activation - composition and table
  installation only.
- `includes/admin/class-admin-controller.php` - safe selection, confirmation,
  guarded action, and result presentation in Changes.
- `tests/` - execution state, schema, conflict, source-preservation, and Admin
  contract assertions.
- `docs/`, `readme.md`, and `languages/` - direct-only limits and translations.

## Data / contracts

- A reviewed `MigrationPlan` gains `field_key`, which must match the same direct
  field key before and after the rename. It is load-bearing because ACF stores a
  companion reference meta key alongside a field value.
- `MigrationExecution` contains `id`, `plan_id`, plan fingerprint/hash,
  initiator and timestamps, requested/copied/skipped/conflicted/failed counts,
  and a terminal status. It stores no `meta_value`.
- `MigrationBackupJournal` contains execution ID, post ID, source and target
  meta IDs/keys, and source and target reference meta IDs/keys. It never stores
  an original, copied, or value-derived field payload.
- The executor may query a source `meta_value` solely to copy it into the new
  direct key. It must not return, log, serialize, render, or store that value in
  plugin-owned tables.
- A candidate is eligible only when the exact old value key and `_old` ACF
  reference key are present, the target value and `_new` reference keys are
  absent, the plan is reviewed and fresh, and its post ID appears in the
  administrator's submitted bounded selection.
- The executor inserts `new_name` and `_new_name` only after validation. If the
  companion reference write fails, it removes the just-created target value key
  for that record. Old keys are never updated or deleted.

## Testing

- Extend the assertion runner with execution-plan, direct-meta repository, and
  Admin action contracts covering zero/duplicate/over-limit selections, stale
  plans, conflicts, missing reference keys, write failures, and no-value table
  schemas.
- Run `php -l` on changed PHP files, `msgfmt --check` for Polish, and
  `sh wp-content/plugins/acf-schema-guard/tests/run-assertions.sh`.
- In Local WordPress, create a disposable direct field rename with two eligible
  records and one target conflict. Execute only one selected record, then verify
  its old value/reference keys remain, its new keys exist, the unchecked record
  is untouched, and the conflicting record is skipped. Inspect tables only for
  identifiers and metadata only.

## Notes for the AI

- Use `SafeRenamePlanner` evidence only as an entry point. Re-query every
  selected row before writing; never trust a browser-supplied count or record.
- Fail closed. Any missing field key, hash mismatch, changed schema, stale plan,
  duplicate target, missing source reference, or unsupported nested ancestry
  means no write for that record.
- Keep each candidate action bounded to 20 and direct post meta only. Do not add
  a "select all" or automatic retry.
- Use prepared WordPress database operations and preserve established escaping,
  nonces, capabilities, and the ACF-inspired Admin styling.
- Feature 29c must be able to roll back only journaled target meta IDs. Do not
  add a rollback button in this feature.
