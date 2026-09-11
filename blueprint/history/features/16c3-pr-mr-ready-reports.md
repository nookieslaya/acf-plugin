# Feature: PR/MR-ready reports

**From build-plan:** feature 16c3
**Status:** verified

## Delivered

- Pro-only release-report export in Markdown or JSON.
- Stable policy and approved-exception information alongside classified findings.
- Optional repository-owned Markdown template with protected summary and finding
  markers, preserving team header, footer, links, and checklist.
- Explicit output paths and overwrite protection.
- English and Polish operating instructions.

## Verification

- `sh wp-content/plugins/acf-schema-guard/tests/run-assertions.sh` passed.
