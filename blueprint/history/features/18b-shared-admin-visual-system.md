# Feature: Shared admin visual system

**From build-plan:** feature 18b
**Status:** verified

## Goal

Make every ACF Schema Guard Admin screen feel like one coherent workspace by
applying the Overview and Changes visual language to History, Field Groups,
Code Usage, Settings, and their empty states.

## Design reference

- User-provided ACF Pro Field Groups screenshot in this conversation.
- The completed Overview and Changes screens as the internal visual reference.

## Build steps

- [x] **Step 1 - Normalize page shells and History** - added a structured
  capture action, readable snapshot history, and useful empty state.
- [x] **Step 2 - Refine Field Groups, Code Usage, and Settings** - added a
  shared rhythm for source health, filters, source selection, and empty states.
- [x] **Step 3 - Responsive integration proof** - verified the scoped mobile
  layouts and added presentation assertions.
- [x] **Step 4 - Repair mobile control regressions** - corrected History
  alignment, Settings checkbox visibility, and full-width status pills.
- [x] **Step 5 - Remove duplicate checkbox rendering** - provided one scoped
  checkbox treatment and fixed the History empty state alignment.
- [x] **Step 6 - Label mobile snapshot cells** - added mobile table labels and
  stopped unlabeled cells from creating an empty pseudo-column.

## Data / contracts

No new data contract. Existing snapshot, baseline, source-health, and scanner
services remain the only source of screen data and actions.

## Verify

- `php -l wp-content/plugins/acf-schema-guard/includes/admin/class-admin-controller.php`
- `sh wp-content/plugins/acf-schema-guard/tests/run-assertions.sh`
- Manual path: inspect Overview, Changes, History, Field Groups, Code Usage,
  and Settings at desktop and narrow widths. Forms must submit to their existing
  routes; read-only pages must have no side effects.
