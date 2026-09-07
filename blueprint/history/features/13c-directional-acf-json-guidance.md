# Feature: Directional ACF JSON sync guidance

**From build-plan:** feature 13c
**Status:** verified

## Completed work

- Added `json_newer`, `database_newer`, `equal`, and `unknown` direction metadata to Source health findings.
- Read ACF JSON `modified` and WordPress database modified time without writes.
- Preserved semantic schema comparison independently of timestamp metadata.
- Updated Field Groups guidance to recommend ACF Sync only for JSON newer and saving the ACF group for database newer.
- Added direction assertions and manually verified real Admin output.

## Verification

- PHP lint passed for changed files.
- `sh wp-content/plugins/acf-schema-guard/tests/run-assertions.sh` passed.
- Manual Field Groups output showed database-newer guidance for two divergent groups and safe manual-review guidance when a timestamp direction was unavailable.
