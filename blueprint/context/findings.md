# Findings

> **Generated file.** The findings ledger: review findings raised by `/audit`
> against the work in progress, each with a durable ID, severity (P0-P3), and
> status. `/implement` marks repaired findings `fixed`, a later `/audit` pass
> moves them to `closed`, and `/complete` refuses to merge while any P0 or P1
> finding is `open` or `fixed`, then archives resolved findings with the work
> and resets this file.

### F-01 [P2] open - Dynamic screen labels cannot be extracted for translation

**File:** `wp-content/plugins/acf-schema-guard/includes/admin/class-admin-controller.php:143`
**Found:** 2026-09-10 by /audit (scope: product architecture and release-readiness; lens: quality)
**Why it matters:** Screen labels are passed to `__()` through array variables.
WordPress translation extraction requires literal source strings, so these labels
will not reliably appear in a POT file. The current English-first Admin UI is
therefore harder to localize for a public plugin release.
**Suggested fix:** Store already translated labels at registration time using
literal translation calls, or map each screen slug to literal translated strings
in the menu-registration method; add a translation extraction check when release
packaging is introduced.
**Resolution:**
