# Feature: Nested-structure explanations

**From build-plan:** feature 11b2
**Status:** verified

## Goal

Explain changes inside repeater, group, and Flexible Content fields in language
that identifies the affected nested field or layout. Keep every structural change
deterministic and avoid repeating child-field changes in their parent result.

## In scope

- Identify field changes below a field group as nested-field changes by using the
  stable schema-change path.
- Describe added, removed, and modified Flexible Content layouts by stable layout
  key and readable label or name.
- Explain layout metadata and normalized setting changes without including the
  layout's `sub_fields` payload.
- Keep child-field findings separate so repeater, group, and layout children are
  each explained once.
- Handle missing or malformed nested arrays without warnings or fatal errors.

## Out of scope

- Rendering explanations in WordPress Admin or WP-CLI, deferred to feature 11c.
- Severity colours, grouping, and other Admin presentation, deferred to feature
  11d.
- Changes to schema normalization, snapshot persistence, risk classification, or
  the ACF test theme.
- Inferring field renames when ACF keys differ.

## Build loop

Build one step at a time, never the whole feature at once.

1. Plan mode lays out the step before any code.
2. The AI implements just that step.
3. It shows the diff (not full files); you read it and understand it.
4. You approve, then choose whether to commit a checkpoint or roll straight on.
   Checkpoints are optional; `/complete` makes the real feature-level commit at the end.

Never accept a step you haven't read. If a diff is too big to review, the step was too big, so split it.

## Build steps

- [x] **Step 1 - Explain nested fields** - use the schema-change path to label
  added, removed, and modified repeater, group, and layout child fields as nested
  fields while preserving the existing explanation contract and deterministic
  property order. Add focused assertions for direct and deeply nested fields.
  *Done when:* nested changes have explicit `Nested field` summaries and
  descriptions, while top-level field output remains unchanged.
- [x] **Step 2 - Explain Flexible Content layouts** - compare layouts by stable
  key within a modified field and describe layout additions, removals, metadata,
  and setting changes. Exclude `sub_fields` from parent layout details because
  the differ already emits their field changes separately. Add assertions for
  added, removed, modified, unchanged, empty, and malformed layout collections.
  *Done when:* layout changes produce stable readable details, child-only layout
  changes do not create a duplicate parent detail, all plugin assertion scripts
  pass, changed PHP files pass `php -l`, and `git diff --check` passes.

## Files / areas

- `wp-content/plugins/acf-schema-guard/includes/diff/class-schema-change-explainer.php`
- `wp-content/plugins/acf-schema-guard/tests/schema-change-explainer-assertions.php`
- `blueprint/context/current-feature.md`

## Data / contracts

- Preserve the public explainer result shape: `array{summary:string,details:string[]}`.
- Consume the existing `SchemaChange::to_array()` keys: `kind`, `node_type`,
  `path`, `before`, and `after`.
- Match layouts only by their normalized stable `key`; do not guess matches from
  mutable names or labels.
- Emit details in deterministic order: existing field properties, field settings,
  then layouts sorted by key and layout property.

## Testing

- There is no configured test runner or Verify command in `AGENTS.md`.
- Extended the existing isolated PHP assertion script for nested paths and layout
  changes, including malformed and child-only inputs.
- Ran `php -l` on every changed PHP file.
- Ran every existing `tests/*-assertion*.php` script as the repository's current
  regression check.
- Ran `git diff --check` before review and completion.

## Notes for the AI

- Follow WordPress PHP coding standards and keep source compatible with the
  plugin's existing PHP style.
- Treat normalized schema arrays as untrusted input at this presentation-neutral
  boundary.
- Do not change `SchemaDiffer` unless a failing contract proves the current
  emitted child paths are insufficient.
- Do not include raw `sub_fields` or whole `layouts` JSON in readable details.
- Do not touch the user's local ACF JSON test changes.
