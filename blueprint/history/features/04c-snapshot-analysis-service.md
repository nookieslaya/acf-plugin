# Feature: Snapshot analysis service

**From build-plan:** feature 4c
**Status:** complete

## Goal

Compose the existing schema differ and risk classifier into one read-only,
deterministic analysis result for two schema snapshots.

## In scope

- Immutable `SnapshotAnalysis` result containing both the diff and risk findings.
- A service accepting two `SchemaSnapshot` instances and returning one analysis.
- Validation that compared snapshots use the supported schema version.
- A lazy plugin seam for future Admin and WP-CLI consumers.
- Isolated assertions for no-change, nested change, and ordered findings.

## Out of scope

- Reading snapshots from the repository, capture, database writes, Admin UI,
  CLI commands, policy configuration, or new risk rules.

## Build steps

- [x] **Step 1 - Analysis result contract** - add an immutable result that keeps a diff and its findings together. *Done when:* consumers can obtain deterministic arrays without re-running classification.
- [x] **Step 2 - Snapshot analysis service** - compare two snapshots and classify the resulting changes. *Done when:* unchanged snapshots return no findings and nested changed fields produce findings.
- [x] **Step 3 - Plugin seam and verification** - expose the read-only service and add durable assertions plus PHP lint. *Done when:* later UI and CLI code has one method that accepts two snapshots and performs no database write.

## Testing

No runner is configured. Add isolated PHP assertions for stable no-change and
nested type-change results; run `php -l` for all plugin PHP files.

## Notes for the AI

- PHP 7.4 compatible. Do not query the repository or WordPress globals.
- Preserve the existing diff and classifier contracts; 4c only composes them.
