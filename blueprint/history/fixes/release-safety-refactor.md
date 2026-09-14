# Fix: Refactor dense release-safety services

**Status:** verified

## Goal

Improve maintainability of the dense policy, report, and unused-field services
without changing their public behaviour.

## Steps

- [x] Split complex methods into focused helpers and retain existing contracts.
- [x] Run full Verify and re-review the changed code.

## Verification

- `sh wp-content/plugins/acf-schema-guard/tests/run-assertions.sh` passed on
  2026-09-14.
- Focused quality and test review covered the risk-policy service, release-report
  command, unused-field inventory, and their existing assertions. No new findings
  were identified in the refactor scope.

