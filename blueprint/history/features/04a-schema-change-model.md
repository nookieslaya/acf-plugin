# Current Feature

> **Generated file.** Holds the one feature, fix, or rollback being built right now. Run
> `/feature <number-or-name>` to spec a build-plan feature, or `/fix "<bug>"` for
> an ad-hoc fix. Use `/rollback <completed-feature>` to plan a safe reversal.
> Build one thing at a time; `/complete` archives it under
> `blueprint/history/` and resets this file.

# Feature: Schema change model

**From build-plan:** feature 4a
**Status:** complete

## Goal

Compare two immutable normalized snapshots and return a deterministic, storage-
independent model of added, removed, and modified field groups and fields.

## In scope

- Immutable change and diff result contracts.
- Matching field groups and nested fields by stable ACF key.
- Added, removed, and modified changes for groups and fields.
- Deterministic ordering and before/after payloads from normalized schemas.
- Isolated assertions for nested fields and no-change inputs.

## Out of scope

- Risk severity, policy rules, database access, capture, Admin UI, CLI, or code scanning.

## Build steps

- [x] **Step 1 - Change contracts** - add immutable diff result and change value objects. *Done when:* changes have stable paths, kinds, before/after values, and deterministic output.
- [x] **Step 2 - Group and field diff** - compare field groups and recursive fields by key. *Done when:* fixtures prove added, removed, modified, and unchanged nested fields.
- [x] **Step 3 - Plugin seam and verification** - expose a read-only diff service and durable assertions. *Done when:* later risk rules receive one complete deterministic diff without storage access.

## Data / contracts

Each change contains `kind` (`added`, `removed`, `modified`), `node_type`
(`field_group`, `field`), a stable key path, and nullable canonical `before`
and `after` arrays. Modified fields expose only a changed node here; 4b owns
interpretation and severity.

## Testing

No test runner is configured. Use isolated PHP assertions for stable ordering,
empty diffs, nested repeater and Flexible Content fields, and changed generic
settings. Run `php -l` for plugin PHP files.

## Notes for the AI

- PHP 7.4 compatible; do not read WordPress globals or snapshot tables.
- Input is `SchemaSnapshot::schema()` or canonical schema arrays only.
- Match by ACF `key`, not label or name. Preserve canonical values exactly.
