# Findings

> **Generated file.** The findings ledger: review findings raised by `/audit`
> against the work in progress, each with a durable ID, severity (P0-P3), and
> status. `/implement` marks repaired findings `fixed`, a later `/audit` pass
> moves them to `closed`, and `/complete` refuses to merge while any P0 or P1
> finding is `open` or `fixed`, then archives resolved findings with the work
> and resets this file.

_No findings recorded. `/audit` appends findings here when it finds them._

### F-01 [P1] closed - Flexible Content subfields are not recursively diffed

**File:** wp-content/plugins/acf-schema-guard/includes/diff/class-schema-differ.php:13
**Found:** 2026-08-31 by /audit (scope: full; lens: quality, security, performance, tests)
**Why it matters:** The differ recurses only through `fields`. A Flexible Content field keeps nested fields under each `layouts[*].sub_fields`, so changes inside a layout become one broad modified Flexible Content field rather than field-level changes. Risk rules then cannot classify those nested type changes precisely.
**Suggested fix:** Traverse layouts by layout key and recurse into each layout's `sub_fields`, retaining a layout segment in the stable field path.
**Resolution:** 2026-08-31: `SchemaDiffer` now traverses matching layouts by key and recursively compares `sub_fields`; the regression assertion covers a nested type change.
