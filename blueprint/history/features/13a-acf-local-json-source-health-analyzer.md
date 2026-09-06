# Feature: ACF Local JSON source-health analyzer

**From build-plan:** feature 13a
**Status:** verified

## Goal

Provide a read-only domain service that compares the field-group definitions stored in WordPress with the configured ACF Local JSON files. It gives the future Admin view a reliable answer about whether each group is safe to share through Git.

## In scope

- Read ACF field-group definitions from the WordPress database without changing posts, fields, JSON files, options, or snapshots.
- Read configured Local JSON files and match groups by their stable ACF group key.
- Classify every discovered key as `aligned`, `database_only`, `json_only`, or `divergent`.
- Treat two independently normalized groups as aligned only when their comparable schema contracts are equal, not merely because their keys match.
- Expose the read-only result through the plugin composition root for feature 13b.
- Add focused PHP assertion coverage for every status, missing ACF support, and malformed or unreadable JSON input.

## Out of scope

- Rendering a Source health Admin screen, notices, buttons, or colours. That is feature 13b.
- Writing, importing, synchronizing, deleting, or repairing ACF database data or Local JSON files.
- Generating PHP ACF definitions.
- Persisting a second history of source-health checks. Immutable schema snapshots remain the history mechanism.
- CI-provider configuration or a new WP-CLI command.

## Build steps

- [x] **Step 1 - Define source-health contracts and pure classification** - add immutable finding/result contracts and a deterministic analyzer that matches independently supplied database and Local JSON schemas by field-group key. *Done when:* the analyzer reports all four statuses deterministically and its assertion test covers aligned, database-only, JSON-only, and divergent groups.
- [x] **Step 2 - Add read-only ACF and Local JSON adapters** - use WordPress and ACF read APIs plus configured `load_json` paths to build independent source inputs; skip unreadable JSON safely and return an unavailable result when ACF is unavailable. *Done when:* no database or file writes occur, and assertions prove unavailable and malformed-input handling.
- [x] **Step 3 - Wire the service into the plugin** - expose a fresh `source_health()` result from the composition root without changing existing snapshot, baseline, CLI, or Admin behavior. *Done when:* the plugin can obtain a result through the new public service boundary and the full Verify command passes.

## Files / areas

- `wp-content/plugins/acf-schema-guard/includes/acf/` - source readers and source-health contracts/analyzer.
- `wp-content/plugins/acf-schema-guard/includes/class-plugin.php` - explicit composition-root wiring only.
- `wp-content/plugins/acf-schema-guard/tests/` - focused source-health assertions, executed by the existing runner.

## Data / contracts

- A source-health finding has `field_group_key`, `title`, `status`, `database_group`, and `json_group`; either representation is `null` when that source does not contain the key.
- `status` is exactly one of `aligned`, `database_only`, `json_only`, or `divergent`.
- The analyzer receives source data independently. It must not infer database state from ACF's merged runtime result, because that can conceal a divergence.
- Local JSON parsing accepts only valid group documents with a non-empty `key`. Invalid or unreadable files are ignored safely in this first diagnostic slice; surfacing file-level parse errors is deferred until a dedicated diagnostics feature is planned.
- ACF absence produces a non-mutating unavailable result, rather than an exception or a false healthy classification.

## Testing

- `source-health-assertions.php` exercises pure status classification, malformed Local JSON, and independent source inputs.
- `source-health-unavailable-assertions.php` covers unavailable ACF.
- `plugin-source-health-assertions.php` covers the public composition boundary.
- `sh wp-content/plugins/acf-schema-guard/tests/run-assertions.sh` passed before completion.
- `php -l` passed for all changed PHP files.

## Notes for the AI

- Use stable ACF field-group keys, never titles or filenames, as identities.
- Keep reads behind small adapters and use WordPress APIs instead of direct SQL.
- Normalize database and JSON groups independently before comparing them, so formatting and key ordering do not create false divergence.
- Do not change the existing `AcfEnvironmentProvider` semantics: its runtime descriptors are not proof of database state.
- Preserve the user's uncommitted theme and `acf-json` changes.
