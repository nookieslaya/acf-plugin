# Feature: Snapshot comparison

**From build-plan:** feature 10b
**Status:** verified

## Goal

Let administrators select two stored snapshots and review the existing
classified schema analysis in WordPress Admin without using WP-CLI.

## Completed steps

- [x] Compose the existing plugin analysis boundary into `AdminController`.
- [x] Render a read-only Changes form with two snapshot selectors and validate
  missing, equal, malformed, or unavailable IDs before analysis.
- [x] Render classified findings with kind, node type, path, severity, and
  rationale, plus a no-changes state.

## Contracts

- GET keys: `before_snapshot` and `after_snapshot`.
- Both IDs must resolve to distinct stored snapshots before analysis.
- Admin uses the existing `SnapshotAnalysis::to_array()` output rather than
  reimplementing diff or risk rules.

## Verification

- PHP lint passed for changed files.
- All eleven assertion scripts passed.
- `git diff --check` passed.
- Local rendering proved the `high` field-type-change result, the no-changes
  state, and validation messages for equal and malformed IDs.

## Out of scope

- Snapshot capture, deletion, export, code-usage correlation, filtering, REST,
  AJAX, and settings.
