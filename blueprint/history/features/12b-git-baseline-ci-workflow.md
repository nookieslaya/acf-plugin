# Feature: Git baseline and CI workflow

**From build-plan:** feature 12b
**Status:** verified

## Completed work

- Added a versioned portable JSON baseline format with strict read validation and
  explicit overwrite protection.
- Added `wp acf-schema-guard baseline export <path>` and
  `wp acf-schema-guard baseline check <path>` commands.
- Made baseline checks compare the committed schema with the effective ACF
  runtime schema without reading or writing the snapshot table.
- Added `--format=json`, `--fail-on-breaking`, and PHP assertion coverage for
  the file format and CI-oriented command behaviour.
- Updated the GitHub Actions and GitLab CI examples to use a checked-in
  `acf-schema-baseline.json` file instead of local snapshot IDs.

## Verification

- All repository PHP assertion scripts, PHP lint, and `git diff --check` passed.
- Local WordPress proof exported a temporary baseline and returned
  `No schema changes found.` when compared with the unchanged effective schema.
- A separate temporary baseline with a deliberately altered field type produced
  a `high` finding and `Error: Breaking schema changes found.` with
  `--fail-on-breaking`.

## Manual use

```sh
wp acf-schema-guard baseline export acf-schema-baseline.json
git add acf-schema-baseline.json
wp acf-schema-guard baseline check acf-schema-baseline.json --fail-on-breaking
```
