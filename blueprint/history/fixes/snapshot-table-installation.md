# Fix: Install snapshot table reliably

**Type:** Fix
**Status:** verified

## Problem

MySQL 8.4 treats `schema` as reserved, so the unquoted column in the table DDL
prevented table creation. The CI check also printed breaking changes but exited
with code 0.

## Resolution

- Quote the `schema` column in table DDL and repository select queries.
- Verify the table after `dbDelta()` and fall back to the same direct DDL when
  needed.
- Let WP-CLI terminate with its normal non-zero error status for breaking risks.

## Verification

All eleven assertion scripts passed. A live Local test created snapshots, found
a high-risk field type change, and confirmed `check --fail-on-breaking` exits 1.
