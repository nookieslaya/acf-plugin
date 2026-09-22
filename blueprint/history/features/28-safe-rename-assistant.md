# Feature: Safe Rename Assistant

**From build-plan:** feature 28
**Status:** verified

## Goal

Turn a detected direct ACF field-name rename into a read-only repair plan in
Changes. It must consolidate the existing code-reference and stored-data
evidence, show a truthful migration dry-run scope, and guide a developer
through verification and baseline replacement without changing ACF definitions,
post meta, or content.

## In scope

- Recognize only a modified `field` whose non-empty `before.name` and
  `after.name` differ as a rename candidate.
- Create a deterministic read-only rename plan with the old and new names,
  literal code references, direct stored-data count, and an ordered checklist.
- For direct post-meta storage, report the dry-run scope as records that have
  the old key, records already carrying the new key (conflicts), and records
  that would need a later migration decision. Never read values.
- Explicitly mark nested, unknown, missing, malformed, and no-data cases as
  not safely simulatable rather than estimating a migration.
- Render the plan next to the existing finding in Changes, using the existing
  Admin visual system and an expandable explanation rather than a new page.
- Add a focused PHP model/service and bounded WordPress repository query,
  assertion coverage, English and Polish copy, and documentation for version
  1.1.0.

## Out of scope

- Copying, renaming, deleting, serializing, or otherwise modifying post meta,
  ACF definitions, options, users, terms, comments, or content.
- A real migration command, automatic migration, scheduled migration, backup,
  rollback, or data-value preview.
- Inference for dynamic PHP calls, custom wrappers, or fields nested in ACF
  Group, Repeater, Flexible Content, Clone, or unsupported structures.
- Any license gate. This is a Free 1.1.0 usability feature.

## Build loop

Build one reviewable step at a time. The user has asked for an end-to-end
implementation, but each step remains independently tested and recorded.

## Build steps

- [x] **Step 1 - rename-plan and dry-run contracts** - add immutable rename
  plan/result models, a planner that accepts only direct field renames, and a
  bounded repository contract that counts old-key records and same-record
  new-key conflicts without loading values. *Done when:* a rename plan has
  deterministic names, scope states, counts, and a no-write guarantee; invalid,
  non-rename, nested, and unknown inputs explicitly produce no simulation.
- [x] **Step 2 - WordPress evidence and plugin composition** - implement the
  prepared, read-only post-meta query and expose a callback from the plugin to
  the Admin controller. *Done when:* direct candidates report total old-key
  records, conflict count, and safe record identifiers without querying
  `meta_value`; all repository queries are bounded and parameterized.
- [x] **Step 3 - Changes repair-plan experience** - render an accessible Safe
  Rename Assistant section alongside an eligible finding, with code references,
  dry-run scope, conflict warning, nested limitation, and ordered deployment
  checklist. *Done when:* an eligible rename has a clear next action, while
  other findings retain their existing presentation and no assistant appears.
- [x] **Step 4 - documentation, localisation, and evidence** - describe the
  read-only feature, its limits, and its future migration boundary in English
  and Polish documentation; update translations and run all assertions. *Done
  when:* the Admin, docs, and tests use the same no-write promise.
- [x] **Repair - complete Polish runtime translations** - remove stale fuzzy
  entries and complete the Polish strings used by the rename plan. *Done when:*
  the compiled `pl_PL` catalogue has no fuzzy entries and the new screen is
  rendered in Polish.

## Files / areas

- `wp-content/plugins/acf-schema-guard/includes/impact/` - rename plan model,
  planner, dry-run repository contract, and WordPress implementation.
- `wp-content/plugins/acf-schema-guard/includes/class-plugin.php` - compose the
  read-only rename assistant with existing impact services.
- `wp-content/plugins/acf-schema-guard/includes/admin/class-admin-controller.php`
  - request the plan for current findings and render it in Changes.
- `wp-content/plugins/acf-schema-guard/tests/` - direct, conflict, unsupported,
  no-value-query, and Admin rendering assertions.
- `wp-content/plugins/acf-schema-guard/docs/`, `readme.md`, and `languages/` -
  version 1.1 feature guidance and translations.

## Data / contracts

- `RenamePlan` is transient, derived per Changes request, and is never stored.
- A candidate has `old_name`, `new_name`, `change`, `code_references`, and a
  `dry_run` result.
- `dry_run.status` is `ready`, `no_records`, `conflicts`, or `not_supported`.
- `dry_run.old_record_count` counts direct post-meta records using the old key.
- `dry_run.conflict_count` counts records that use both old and new direct keys.
- `dry_run.migration_candidate_count` is
  `max( 0, old_record_count - conflict_count )`; it is a planning count, not a
  write forecast or an instruction to migrate automatically.
- The repository returns only bounded record identifiers. It must not select,
  load, log, or render `meta_value`.

## Testing

- Add focused assertions for rename-candidate validation, deterministic
  dry-run states, conflict arithmetic, unsupported nested storage, and no-value
  query constraints.
- Add Admin rendering assertions for the plan and its no-write limitation.
- Run PHP lint on changed PHP files, `msgfmt --check`, and
  `sh wp-content/plugins/acf-schema-guard/tests/run-assertions.sh`.
- Manual review: create a direct field rename with at least one old-key record,
  optionally give one record the new key too, then open Changes and confirm that
  the plan and counts match without any data changing.

## Notes for the AI

- Reuse existing code-impact and stored-data evidence; do not duplicate their
  scanner or broad data inventory logic.
- A difference in field name is not proof that data should be migrated. Use
  language such as "would require a migration decision", never "will migrate".
- Restrict direct simulation to exact post-meta keys. The helper must say why a
  nested or unknown structure cannot be safely simulated.
- Follow WordPress escaping, capability boundaries, and the existing
  ACF-Pro-inspired visual system. No endpoint or write action is needed.
