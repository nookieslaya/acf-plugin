# Fix: Refactor dense release-safety services

**Status:** in progress

## Goal

Improve maintainability of the dense policy, report, and unused-field services
without changing their public behaviour.

## Steps

- [ ] Split complex methods into focused helpers and retain existing contracts.
- [ ] Run full Verify and re-review the changed code.
