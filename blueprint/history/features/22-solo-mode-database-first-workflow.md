# Feature: Solo Mode - database-first workflow

**From build-plan:** feature 22
**Status:** verified

## Delivered

- A `database_first` source mode when ACF has no configured Local JSON paths.
- A non-error Source Health report for Solo Mode while preserving normal JSON
  comparison whenever ACF Local JSON is configured.
- Overview and Field Groups guidance for baseline, live Changes, and Code Usage
  without requiring Local JSON.
- Git and CI guidance based on the optional committed plugin baseline export.

## Verification

- `php wp-content/plugins/acf-schema-guard/tests/solo-source-mode-assertions.php`
- `sh wp-content/plugins/acf-schema-guard/tests/run-assertions.sh`
- `php -l` for changed source-mode, source-health, and Admin files.

## Scope boundary

- Solo Mode is Free.
- It does not change ACF configuration, Local JSON paths, snapshots, or CI
  behaviour. It only reports the configured source workflow accurately.
