# Normalize ACF runtime defaults in Source Health

**Type:** Fix

**Status:** verified and locally merged

## The problem

Source Health reported false divergence after an ACF Local JSON sync because ACF
database reads injected runtime metadata and defaults not present in JSON. A
second defect meant the database side used `acf_get_fields()`, which could be
overridden by Local JSON and mask a real database versus JSON difference.

## The fix

- Added Source Health-only normalization for confirmed ACF runtime metadata and
  equivalent empty defaults.
- Reads database fields through `acf_get_raw_fields()` and reconstructs nested
  Group, Repeater, and Flexible Content structures without Local JSON overrides.
- Preserved true differences in field names, types, settings, structures, and
  rules.
- Improved Field Groups to distinguish matching sources, the newer source,
  missing sources, and equal-timestamp conflicts.
- Explains that ACF Sync may be unavailable when definitions differ but their
  modification timestamps are equal.

## Verification

- Full `sh wp-content/plugins/acf-schema-guard/tests/run-assertions.sh` passed.
- PHP lint passed for each changed production PHP file.
- Live Local WordPress checks confirmed both cases:
  - synced Card, Hero, Features, and Flexible Content groups report `Aligned`;
  - a JSON-only Card rename reports `Divergent`, with raw database name
    `card_title_area` and JSON name `card_title_area1`.

## Data safety

The production fix does not write ACF, Local JSON, WordPress content, or
snapshots. Temporary local test content used during manual verification was
removed before completion.
