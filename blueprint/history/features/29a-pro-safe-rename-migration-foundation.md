# Feature: Pro safe rename migration foundation

**From build-plan:** feature 29a
**Status:** verified

## Goal

Create the durable, capability-gated foundation for a future Pro migration of
one direct ACF field-name rename. Administrators can prepare and review a
value-free migration plan. Free users retain all existing detection, evidence,
and read-only Safe Rename Assistant workflows, while no version of this feature
may copy, rename, delete, or read WordPress field values.

## In scope

- Add the named Pro capability `safe_rename_migrations` without changing the
  existing Free availability of already shipped workflows.
- Make the new capability unavailable by default outside the existing local
  Pro-preview path, while retaining the provider-neutral entitlement filter for
  feature 25.
- Define a durable migration-plan model and a dedicated WordPress table that
  persist only direct-rename metadata, schema identity, bounded counts, user
  audit data, and review state.
- Accept only a currently detected, direct field-name rename with two non-empty,
  different names. Nested, malformed, stale, missing, or unsupported findings
  cannot create a plan.
- Add a secure administrator action to prepare a draft plan from an eligible
  Changes finding, and a separate review decision with a required note.
- Render the Free/Pro boundary and plan state alongside the existing Safe Rename
  Assistant without exposing any migration execution control.
- Add assertions, English and Polish copy, and documentation that make the
  no-value and no-data-write boundary explicit.

## Out of scope

- Copying, renaming, deleting, serializing, or reading `postmeta` values.
- Backup payloads, execution, execution reports, rollback, scheduled jobs,
  bulk selection, WP-CLI migration commands, REST endpoints, or cron jobs.
- Nested ACF fields, options, users, terms, comments, custom tables, and dynamic
  PHP references.
- Provider integration, customer activation, expiry, billing, or other feature
  25 licensing work.

## Build loop

Build one reviewable step at a time. Each step keeps the existing read-only
rename analysis available and uses the repository assertion runner before review.

## Build steps

- [x] **Step 1 - Pro capability and migration-plan contract** - add the new
  capability, preserve existing Free capabilities, and create immutable plan,
  status, and repository contracts with a dedicated plugin table. *Done when:*
  an unlicensed production state denies only the new capability, local preview
  can enable it, and a plan stores no field values or record-value payloads.
- [x] **Step 2 - eligible-plan preparation and review service** - derive a plan
  only from a fresh direct rename finding, preserve its schema fingerprint and
  bounded evidence counts, and require a review note before changing its state.
  *Done when:* nested, stale, malformed, no-data, and non-rename inputs are
  rejected deterministically; no service reads or writes WordPress content data.
- [x] **Step 3 - secure Changes integration** - add capability- and
  `manage_options`-guarded nonce actions for drafting and reviewing a plan, then
  render its status or a clear Pro notice beside the Safe Rename Assistant.
  *Done when:* Free users can still see all risk evidence but cannot invoke a
  write endpoint; direct requests without capability, nonce, or valid finding
  are denied; the screen contains no execute or migrate action.
- [x] **Step 4 - documentation, localisation, and verification** - document the
  Free/Pro boundary and future execution path, update English and Polish
  catalogues, and add focused contract, model, repository, eligibility, and
  admin-access assertions. *Done when:* tests, syntax checks, translations, and
  a Local WordPress plan-creation review prove that only plugin plan metadata is
  written.

## Files / areas

- `wp-content/plugins/acf-schema-guard/includes/licensing/` - named capability
  and default-entitlement boundary.
- `wp-content/plugins/acf-schema-guard/includes/migrations/` - plan model,
  validator, repository contract, WordPress table, and plan-review service.
- `wp-content/plugins/acf-schema-guard/includes/class-plugin.php` - service and
  table composition only.
- `wp-content/plugins/acf-schema-guard/includes/admin/class-admin-controller.php`
  - guarded preparation and review actions plus Changes presentation.
- `wp-content/plugins/acf-schema-guard/tests/` - focused no-value, eligibility,
  capability, repository, and admin contract assertions.
- `wp-content/plugins/acf-schema-guard/docs/`, `readme.md`, and `languages/` -
  user-facing Free/Pro explanation and translations.

## Data / contracts

- `MigrationPlan` is a durable plugin record with `id`, `old_name`, `new_name`,
  `finding_fingerprint`, `baseline_snapshot_id`, `current_schema_hash`,
  `old_record_count`, `conflict_count`, `candidate_count`, `status`, creation
  metadata, and optional review metadata. It never includes a meta value.
- `status` is `draft`, `reviewed`, or `invalid` in this stage. Execution states
  belong to features 29b and 29c.
- `MigrationPlanRepository` may write only the plugin-owned plan table. It must
  not query `meta_value` or modify any WordPress content table.
- A plan is valid only for one direct rename and its source fingerprint. A later
  schema change invalidates the plan instead of silently retargeting it.
- Capability is `safe_rename_migrations`. Existing Free-validation capabilities
  remain available without a production license; this newly added write-path
  capability requires a valid entitlement or the strictly local preview state.

## Testing

- Add unit-style assertions for entitlement separation, valid and invalid plan
  shapes, stale fingerprints, required review notes, and no-value-query source
  constraints.
- Add repository assertions for the exact table schema and parameterized writes
  of identifier-only plan metadata.
- Add Admin source-contract assertions for `manage_options`, capability, nonce,
  and absence of execution controls.
- Run `php -l` on each changed PHP file, `msgfmt --check` on Polish translations,
  and `sh wp-content/plugins/acf-schema-guard/tests/run-assertions.sh`.
- In Local WordPress, create one eligible direct rename, prepare and review its
  plan in Changes, then confirm only the plugin plan table changes while the
  relevant post-meta keys and values remain unchanged.

## Notes for the AI

- Reuse `SafeRenamePlanner` and its direct-only evidence. Do not duplicate the
  PHP scanner or stored-data inventory.
- Any post-meta query in this feature may use only `post_id`, `meta_key`, and
  safe post identifiers. Selecting `meta_value` is prohibited.
- Reject rather than guess. If the underlying rename cannot be matched exactly,
  show an invalid or unavailable state.
- Preserve WordPress escaping, nonces, capability checks, and the established
  ACF-Pro-inspired Admin visual system.
- Feature 29b is the first feature allowed to copy values; feature 29c owns
  rollback and history. Do not pull either concern into 29a.
