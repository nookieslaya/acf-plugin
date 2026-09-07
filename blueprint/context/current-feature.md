# Feature: Code-impact analysis engine

**From build-plan:** feature 14a
**Status:** in progress

## Goal

Connect classified ACF schema changes with PHP ACF usage references so later Admin and CI views can show exactly which files and lines require review.

## In scope

- Define immutable impact records linking a schema change to matching PHP references.
- Detect impacts for removed fields, field-name changes, and field-type changes.
- Report deterministic severity and an actionable explanation per reference.
- Reuse the existing scanner interfaces and change models without rescanning in the engine.
- Add focused assertion coverage for matches, no matches, and duplicate references.

## Out of scope

- Admin UI, filters, export, scan-root settings, or automatic code edits.
- Blade, Twig, JavaScript, and custom wrapper scanning.
- Guessing a rename from two unrelated field keys.

## Build steps

- [x] **Step 1 - Impact contracts and matching rules** - add deterministic impact records and match explicit schema changes to PHP field references. *Done when:* removed, renamed, and type-changed fields receive the correct impact classification.
- [x] **Step 2 - Impact analysis service** - compose schema findings and scanner references into a stable result without side effects. *Done when:* the service handles empty and duplicate inputs predictably.
- [x] **Step 3 - Tests and plugin boundary** - expose the service for future Admin/CLI work and run the full Verify command. *Done when:* focused assertions and the existing runner pass.

## Testing

- Add isolated PHP assertions and run `sh wp-content/plugins/acf-schema-guard/tests/run-assertions.sh`.
- Run `php -l` for changed PHP files.

## Notes for the AI

- A reference proves only a supported literal PHP call site, not runtime reachability.
- Preserve the user's uncommitted theme and `acf-json` changes.
