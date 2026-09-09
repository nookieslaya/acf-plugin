# Feature: Automatic deduplicated schema history

**From build-plan:** feature 17c
**Status:** verified

Automatic snapshots are created after ACF field-group saves only when the
normalized schema differs from the most recent `acf-auto` snapshot. History
labels these entries as **Automatic ACF save**. Manual capture and the approved
baseline remain unchanged.

## Verification

- `sh wp-content/plugins/acf-schema-guard/tests/run-assertions.sh` passed.
- Manual test confirmed no entry for an unchanged save and exactly one automatic
  entry after a schema change.
