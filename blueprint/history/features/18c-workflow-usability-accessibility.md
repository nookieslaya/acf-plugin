# Feature: Workflow usability and accessibility

**From build-plan:** feature 18c
**Status:** verified

## Goal

Make the finished ACF Schema Guard Admin workflows easier to understand and use
with a keyboard, especially when reviewing changes, source health, code
references, snapshots, and scanner settings.

## Design reference

- User-provided ACF Pro Field Groups screenshot in this conversation.
- Completed Overview, Changes, and shared Admin workspace styles.

## Delivered

- [x] **Scoped focus and control states** - added a consistent, visible
  keyboard-focus treatment for ACF Schema Guard links, buttons, selects,
  checkboxes, summaries, and table actions without affecting WordPress or ACF.
- [x] **Clearer expandable workflows** - made Code Usage disclosures state that
  they show call sites and labelled the expanded content as PHP context around
  the selected call.
- [x] **Semantic and responsive safeguards** - retained native details,
  summaries, labels, and table headings; added stable markup assertions and
  preserved mobile table labels.
- [x] **Editor-like PHP context** - styled the expanded PHP preview with a
  language label, line gutter, contrast, and responsive spacing without adding
  a code-editor dependency.

## Data / contracts

No new data contract. The work changes only presentation and accessible guidance
around existing operations.

## Verification

- `sh wp-content/plugins/acf-schema-guard/tests/run-assertions.sh`
- `php -l wp-content/plugins/acf-schema-guard/includes/admin/class-admin-controller.php`
- `php -l wp-content/plugins/acf-schema-guard/tests/admin-snapshot-query-assertions.php`
- `git diff --check`

## Manual review path

Tab through Overview, Changes, History, Field Groups, Code Usage, and Settings.
In Code Usage, open a field and a call site to view its PHP context. On a narrow
viewport, confirm the same actions remain reachable and table cells retain
clear labels.
