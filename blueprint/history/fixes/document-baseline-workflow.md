# Fix: Document the baseline workflow

**Type:** Fix
**Status:** verified

## Problem

The README lacked one complete baseline workflow and still described the Admin
workspace as information-only.

## Completed work

- Replaced the stale Admin description with the current capture, approved
  baseline, and automatic Changes workflow.
- Added complete Git-baseline instructions for initial export, verification,
  first commit, routine comparison, deliberate update, CI usage, severity
  outcomes, and ignored-file handling.
- Documented the boundary between portable Git baselines and WordPress Admin
  snapshot baselines.

## Verification

- Cross-checked command names and options against the WP-CLI registration,
  implementation, and CI examples.
- `git diff --check` passed.
