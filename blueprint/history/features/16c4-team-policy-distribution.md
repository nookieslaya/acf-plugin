# Feature: Team policy distribution

**From build-plan:** feature 16c4
**Status:** verified

## Delivered

- Repository-owned `acf-schema-guard-policy.json` remains the shared policy
  mechanism, without adding a remote synchronization service.
- Settings shows when the Git policy is active, its effective threshold, and its
  precedence over the local site option.
- English and Polish guidance explains the team workflow.

## Verification

- `sh wp-content/plugins/acf-schema-guard/tests/run-assertions.sh` passed.
