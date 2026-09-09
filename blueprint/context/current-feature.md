# Feature: End-to-end release verification

**From build-plan:** feature 15c
**Status:** in progress

## Goal

Prove the complete schema-baseline, change-analysis, code-usage, and export
workflow in the running local WordPress site, then record the tested versions.

## Build steps

- [ ] Run the documented local WordPress release scenario.
- [ ] Record the tested WordPress, PHP, ACF, and ACF PRO versions.
- [ ] Confirm the baseline-to-report workflow and mark feature 15 complete.

## Verify

- `sh wp-content/plugins/acf-schema-guard/tests/run-assertions.sh`
- `wp acf-schema-guard baseline check acf-schema-baseline.json --fail-on-breaking`
