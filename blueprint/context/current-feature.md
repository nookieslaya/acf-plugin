# Feature: Changes-to-code integration

**From build-plan:** feature 14c
**Status:** verified

## Goal

Show concrete PHP ACF references directly beneath each affected schema change, so an administrator can move from an ACF change to the code that needs review without recreating the comparison in Code Usage.

## In scope

- Reuse the current baseline comparison and active-theme scanner at render time.
- Attach removed, renamed, and type-changed field references through the existing code-impact analyzer.
- Show severity, file, line, and expression under the corresponding change.
- Preserve the existing Changes page when there are no impacts or no scanner results.
- Add focused Admin rendering assertions.

## Out of scope

- Code edits, automatic renames, scanner-root settings, or report exports.
- New persistent records for code impacts.
- Linking field-group-only changes to source references.

## Build steps

- [x] **Step 1 - Comparison-to-impact composition** - expose a callback that scans the active theme and analyzes the current comparison findings. *Done when:* only supported field changes receive their matching impact records.
- [x] **Step 2 - Changes-page impact cards** - render grouped, accessible code locations beneath each affected finding. *Done when:* an impacted field shows risk, path, line, and expression, while unrelated findings remain unchanged.
- [x] **Step 3 - Assertions and verification** - cover impacted and empty output, then run the project verification command. *Done when:* assertions, syntax checks, and the full runner pass.

## Testing

- Add Admin rendering assertions for a matching field reference and no-impact state.
- Run `php -l` for changed PHP files.
- Run `sh wp-content/plugins/acf-schema-guard/tests/run-assertions.sh`.

## Notes for the AI

- A code reference means a supported literal PHP call site, not proof of runtime reachability.
- Preserve the user's uncommitted theme and `acf-json` changes.
