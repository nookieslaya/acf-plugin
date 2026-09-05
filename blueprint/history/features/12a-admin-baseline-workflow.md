# Feature: Admin baseline workflow

**From build-plan:** feature 12a
**Status:** verified

## Completed work

- Added a baseline service that stores one approved snapshot UUID and resolves it
  only when the snapshot still exists.
- Added protected History controls for setting and marking the baseline.
- Made Changes compare the approved baseline with the newest stored snapshot
  automatically, including actionable no-baseline and no-newer-snapshot states.

## Verification

- PHP lint, all assertion scripts, and `git diff --check` passed.
- Local rendering confirmed automatic Changes comparison without UUID input.
