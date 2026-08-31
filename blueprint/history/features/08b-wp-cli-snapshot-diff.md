# Feature: WP-CLI snapshot diff

**From build-plan:** feature 8b
**Status:** verified

## Goal

Compare two persisted ACF schema snapshots from WP-CLI using the existing diff
and risk-analysis services.

## Build steps

- [x] CLI-only diff command composition.
- [x] Snapshot resolution, table output, and JSON output.
- [x] Isolated contract test and README documentation.

## Contract

`wp acf-schema-guard diff <before-id> <after-id> [--format=table|json]`

The command is read-only. It reports `kind`, `node_type`, `path`, `severity`,
and `rationale`; missing snapshots use the WP-CLI error path.

## Testing

Lint and all ten plugin assertion scripts passed, including the isolated
WP-CLI diff assertion with fake snapshots and repository.
