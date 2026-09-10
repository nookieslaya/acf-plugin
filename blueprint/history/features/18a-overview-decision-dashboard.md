# Feature: Overview decision dashboard

**From build-plan:** feature 18a
**Status:** verified

## Goal

Replace the placeholder Overview screen with a read-only dashboard that makes
the current ACF schema safety posture clear and directs an administrator to the
next useful ACF Schema Guard workflow.

## Design reference

- User-provided ACF Pro Field Groups screenshot in this conversation, used for
  the restrained table, surface, and blue-action visual language.
- `wp-content/plugins/acf-schema-guard/assets/css/admin.css`, the existing
  scoped ACF Schema Guard visual system.

## In scope

- Show the approved baseline state and its capture date when available.
- Run the existing read-only live-baseline analysis and summarize its finding
  counts by severity, including unavailable and no-baseline states.
- Summarize existing Local JSON source-health statuses without changing either
  the database or JSON files.
- Provide contextual links to existing ACF Schema Guard workflows.
- Align the Changes screen with the dashboard visual language.

## Build steps

- [x] **Step 1 - Assemble read-only dashboard state** - added presentation-safe
  baseline, comparison, and source-health summaries using existing services.
- [x] **Step 2 - Render the Overview decision dashboard** - replaced the
  placeholder with escaped status cards and existing WordPress admin links.
- [x] **Step 3 - Polish and prove the dashboard** - added scoped responsive
  styling and assertions for empty, unavailable, and risk states.
- [x] **Step 4 - Align Changes with the dashboard** - added baseline/current
  context cards and a results heading without changing analysis behavior.

## Data / contracts

- Reuses `LiveBaselineAnalysis`, `SnapshotAnalysis`,
  `BaselineSnapshotService`, and `SourceHealthReport` as read-only inputs.
- Introduces no options, tables, or persisted records.

## Verify

- `php -l wp-content/plugins/acf-schema-guard/includes/admin/class-admin-controller.php`
- `sh wp-content/plugins/acf-schema-guard/tests/run-assertions.sh`
- Manual path: open **ACF Schema Guard → Overview**, resize the browser, and
  follow an action link. Viewing the page must not create a snapshot or alter
  ACF data.
