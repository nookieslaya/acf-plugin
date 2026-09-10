# Fix: Exclude trashed ACF Local JSON artifacts

**Status:** verified

## Problem

ACF `group_*__trashed` artifacts could appear beside an active field group and
produce duplicate Changes and Source Health findings. Historical snapshots that
contained the artifact also reported a false critical removal after source
filtering was introduced.

## Resolution

- Excluded `__trashed` field-group keys from the effective ACF schema.
- Excluded the same keys from database and Local JSON source-health inputs.
- Excluded them from both sides of schema comparisons without changing immutable
  stored snapshots.
- Preserved ordinary inactive groups in all analyses.

## Verification

- `sh wp-content/plugins/acf-schema-guard/tests/run-assertions.sh`
- PHP lint for all changed PHP source files.
- Manual Admin confirmation that historical `__trashed` findings no longer
  appear in Changes.
