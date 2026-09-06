# Fix: Remove obsolete manual snapshot comparison code

**Type:** Fix
**Status:** verified
**Fixes:** F-06

Removed the four unreachable private methods from the former manual snapshot
selector flow. The baseline-driven History and Changes flows remain intact.

## Verification

- All 18 assertion scripts passed.
- `AdminController` passed PHP lint.
- The Admin query assertion confirms the obsolete methods are absent.

## Findings

### remove-obsolete-admin-comparison/F-06 [P3] closed - Obsolete manual-comparison methods remain unreachable

**Resolution:** Removed the obsolete methods, added a regression assertion, and
confirmed no production reference remains in the repair audit.
