# Feature: Current-file code usage

**From build-plan:** feature 17b
**Status:** verified

## Completed work

- Added one read-only service that scans configured source roots from the
  current filesystem for every request.
- Routed Code Usage, Changes code impacts, and JSON/Markdown report export
  through that service.
- Updated documentation and Admin copy to describe current-file scanning.

## Verification

- `sh wp-content/plugins/acf-schema-guard/tests/run-assertions.sh` passed.
- Manual verification confirmed that adding and removing a literal ACF call is
  reflected by refreshing Code Usage without creating a schema snapshot.
