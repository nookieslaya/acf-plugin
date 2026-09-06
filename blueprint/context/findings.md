# Findings

> **Generated file.** The findings ledger: review findings raised by `/audit`
> against the work in progress, each with a durable ID, severity (P0-P3), and
> status. `/implement` marks repaired findings `fixed`, a later `/audit` pass
> moves them to `closed`, and `/complete` refuses to merge while any P0 or P1
> finding is `open` or `fixed`, then archives resolved findings with the work
> and resets this file.

### F-04 [P2] open - Existing assertions are not a declared automated test gate

**File:** AGENTS.md:200
**Found:** 2026-09-06 by /audit (scope: full; lens: quality, security, performance, tests)
**Why it matters:** Sixteen assertion scripts exist and currently pass, but the project declares no test runner, `Verify` command, browser harness, or active CI workflow. Future commits can merge without running the regression suite, including the breaking-change policy and Admin security assertions.
**Suggested fix:** Use the Blueprint tests workflow to define one deterministic test command for all assertion scripts, then use the CI workflow to make that command the repository's `Verify` check. Add browser coverage separately for stable Admin workflows.
**Resolution:**

### F-05 [P2] open - Core domain classes substantially drift from project PHP standards

**File:** wp-content/plugins/acf-schema-guard/includes/diff/class-schema-differ.php:4
**Found:** 2026-09-06 by /audit (scope: full; lens: quality, security, performance, tests)
**Why it matters:** Several central diff, risk, scanner, and CLI classes compress complete methods or classes onto single lines. This conflicts with the documented WordPress coding conventions, hides branches during review, and makes defects such as F-01 and F-02 harder to see and test safely.
**Suggested fix:** Format the affected project classes to the existing readable WordPress style while making the functional repairs, without changing public contracts. Add a locally runnable coding-standard check only if the project deliberately adopts one.
**Resolution:**

### F-06 [P3] open - Obsolete manual-comparison methods remain unreachable

**File:** wp-content/plugins/acf-schema-guard/includes/admin/class-admin-controller.php:308
**Found:** 2026-09-06 by /audit (scope: full; lens: quality, security, performance, tests)
**Why it matters:** `comparison_selection()`, `requested_snapshot_id()`, `render_snapshot_options()`, and `render_comparison_notice()` are no longer called after Changes switched to automatic baseline/current comparison. They retain unused request-processing and rendering paths that increase maintenance surface.
**Suggested fix:** Remove the four private methods and confirm the baseline-driven Changes and History flows still pass their focused tests.
**Resolution:**
