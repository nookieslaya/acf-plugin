# Admin UI polish

**Type:** Fix
**Status:** verified

## The problem

The ACF Schema Guard administration screens used conflicting legacy CSS. Code
Usage filters were misaligned, scanner-root choices were visually loose, and
Field Groups status labels could wrap or overflow. The workspace did not feel
consistent with the visual language of ACF Pro.

## The fix

Use one scoped ACF-inspired admin visual system: calm white surfaces, light
table headers, blue primary actions, compact form controls, non-wrapping status
badges, responsive tables, and clear expand affordances for PHP references.
The change must not alter scanning, snapshots, source-health, or ACF data.

## Build steps

- [x] Replace conflicting plugin-admin CSS with a scoped visual system for
  tables, controls, severity states, source-health statuses, and mobile views.
  **Done when:** the plugin screens use a coherent responsive UI without styling
  WordPress or ACF screens outside ACF Schema Guard.
- [x] Structure Code Usage filter controls and scanner-root choices with
  component classes for reliable alignment and compact spacing. **Done when:**
  labels, selects, checkboxes, and actions remain readable at desktop and
  narrow widths.

## Verify

- `php -l wp-content/plugins/acf-schema-guard/includes/admin/class-admin-controller.php`
- `sh wp-content/plugins/acf-schema-guard/tests/run-assertions.sh`
- Open **ACF Schema Guard → Code Usage**, **Field Groups**, and **Settings**;
  resize the browser and expand a code reference.
