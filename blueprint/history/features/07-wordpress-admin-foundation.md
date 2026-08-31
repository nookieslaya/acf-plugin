# Feature: WordPress Admin foundation

**From build-plan:** feature 7
**Status:** verified

## Goal

Provide a safe, developer-focused entry point in WordPress Admin for ACF Schema
Guard. Administrators can navigate six stable, read-only section screens before
later features add capture, analysis, scanning, and configuration actions.

## In scope

- A top-level `ACF Schema Guard` WordPress Admin menu.
- Six named sections: Overview, Changes, Field Groups, Code Usage, History, and
  Settings.
- A small Admin controller responsible for registration, capability protection,
  and rendering.
- A shared screen definition and renderer with escaped headings, descriptions,
  and a deliberate empty state.
- One stylesheet enqueued only for this plugin's Admin screens.
- A short README section that identifies the currently read-only Admin surface.

## Out of scope

- Capturing snapshots, selecting or comparing snapshots, executing the scanner,
  displaying real results, or linking findings to code.
- Persisted settings, forms, nonces, AJAX, REST routes, WP-CLI, charts, filters,
  pagination, or automatic background work.
- Changing the existing snapshot, diff, risk, or scanner contracts.

## Build steps

- [x] **Step 1 - Admin registration boundary** - create an Admin controller and
  wire it into the plugin bootstrap on `admin_menu`. Register the top-level menu
  and its six named sections, each protected by `manage_options`.
- [x] **Step 2 - Read-only screen renderer** - define the six screen labels,
  descriptions, and empty-state copy in one controller-owned mapping, then render
  them through one escaped callback.
- [x] **Step 3 - Scoped polish and evidence** - add a plugin-owned Admin
  stylesheet, enqueue it only for the registered page hooks, document the screen
  boundary, lint the plugin, and manually inspect the navigation.

## Files / areas

- `wp-content/plugins/acf-schema-guard/includes/admin/class-admin-controller.php`
  - menu registration, screen map, rendering, and stylesheet enqueueing.
- `wp-content/plugins/acf-schema-guard/includes/class-plugin.php` - Admin-only
  controller composition.
- `wp-content/plugins/acf-schema-guard/assets/css/admin.css` - scoped styles.
- `wp-content/plugins/acf-schema-guard/readme.md` - documented read-only scope.

## Data / contracts

- No new database table, option, REST response, or form contract.
- Stable page slugs are `acf-schema-guard`, `acf-schema-guard-changes`,
  `acf-schema-guard-field-groups`, `acf-schema-guard-code-usage`,
  `acf-schema-guard-history`, and `acf-schema-guard-settings`.
- Every page uses and verifies the `manage_options` capability.
- Registered page-hook suffixes are held only in memory for stylesheet loading.

## Testing

- `php -l` passed for the Admin controller, plugin composition root, and plugin
  bootstrap.
- All eight existing PHP assertion scripts passed.
- The user manually opened the WordPress Admin screens and confirmed their
  read-only placeholder behavior.

## Findings

### 07/F-01 [P1] closed - Flexible Content subfields are not recursively diffed

**File:** `wp-content/plugins/acf-schema-guard/includes/diff/class-schema-differ.php:13`

**Found:** 2026-08-31 by `/audit` (scope: full; lens: quality, security,
performance, tests)

**Resolution:** 2026-08-31: `SchemaDiffer` now traverses matching layouts by
key and recursively compares `sub_fields`; the regression assertion covers a
nested type change.
