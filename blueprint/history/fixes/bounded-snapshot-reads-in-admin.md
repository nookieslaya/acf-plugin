# Fix: Bound snapshot reads in Admin

**Type:** Fix
**Status:** verified
**Fixes:** F-03

## The problem

History and Changes loaded the repository's unbounded `all()` result. Each
record included and hydrated a full schema payload, so the memory and database
cost of these Admin pages grew with every captured snapshot.

## The fix

- Added `recent($limit)` for a validated, bounded newest-first snapshot list.
- Added `latest()` for one newest snapshot across all sources.
- History now requests `recent(25)` and Changes requests `latest()`.
- Added repository and Admin contract assertions. The Admin assertion fails if
  either page uses `all()`.

## Verification

- Full PHP lint passed for the plugin and development theme.
- All 18 assertion scripts passed.
- The repair audit confirmed no production Admin path calls `all()` and closed
  F-03.

## Findings

### bounded-snapshot-reads-in-admin/F-03 [P2] closed - Snapshot history loads and hydrates every stored schema

**File:** wp-content/plugins/acf-schema-guard/includes/snapshots/class-wordpress-snapshot-repository.php:86
**Found:** 2026-09-06 by /audit (scope: full; lens: quality, security, performance, tests)
**Why it matters:** Loading every persisted schema makes Admin request cost grow as immutable captures accumulate.
**Suggested fix:** Add bounded history reads and a dedicated newest-snapshot query for Changes.
**Resolution:** Re-reviewed on 2026-09-06. History requests a bounded newest-first `recent(25)` list and Changes requests only `latest()`. Repository assertions verify deterministic `LIMIT` queries, and the Admin query assertion verifies neither production view calls `all()`. Full PHP lint and all 18 assertion scripts pass. Closed by the repair audit.
