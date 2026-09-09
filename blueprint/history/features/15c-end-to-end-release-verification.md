# Feature: End-to-end release verification

**From build-plan:** feature 15c
**Status:** verified

## Goal

Prove the complete schema-baseline, change-analysis, code-usage, and export
workflow in the running local WordPress site, then record the tested versions.

## Completed work

- The full assertion runner passed on 2026-09-09.
- A local manual pass confirmed the Overview, baseline capture, automatic
  Changes comparison, Code Usage snippets, Field Groups source health, and
  report export workflow.
- Compatibility targets, release checklist, changelog, and the repeatable
  manual procedure are documented in the plugin.

## Verification

- `sh wp-content/plugins/acf-schema-guard/tests/run-assertions.sh` passed.
- Manual local WordPress verification was confirmed by the site administrator.

## Findings

### 15c/F-04 [P2] invalid - Existing assertions are not a declared automated test gate

**File:** AGENTS.md:200
**Found:** 2026-09-06 by /audit (scope: full; lens: quality, security, performance, tests)
**Resolution:** Re-examined 2026-09-08: `AGENTS.md` declares the assertion runner as both Test and Verify, and the repository includes a CI verification workflow. The original finding is no longer accurate.

### 15c/F-05 [P2] closed - Core domain classes substantially drift from project PHP standards

**File:** `wp-content/plugins/acf-schema-guard/includes/diff/class-schema-differ.php:4`
**Found:** 2026-09-06 by /audit (scope: full; lens: quality, security, performance, tests)
**Resolution:** Re-audited 2026-09-09. The central diff, risk, scanner, and CLI classes are formatted as readable WordPress-style classes. PHP syntax checks and the complete assertion runner passed.

### 15c/F-06 [P2] closed - Code previews do not resolve configured plugin roots

**File:** `wp-content/plugins/acf-schema-guard/includes/admin/class-admin-controller.php:211`
**Found:** 2026-09-08 by /audit (scope: full; lens: quality, security, performance, tests)
**Resolution:** Re-audited 2026-09-09. References retain their configured scan root and Admin previews resolve files from it. Scanner assertions cover root retention and the full assertion runner passed.
