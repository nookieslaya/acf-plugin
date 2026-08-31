# Feature: CI integration guidance and verification

**From build-plan:** feature 9
**Status:** verified

## Outcome

Repository-owned, non-active GitHub Actions and GitLab CI templates document the
same read-only WP-CLI check contract, prerequisites, and snapshot-ID handoff.

## Verification

Both YAML examples parse locally and `git diff --check` passed. No remote CI,
secrets, runner, database, or workflow configuration was changed.
