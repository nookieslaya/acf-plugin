# Feature: Translation-ready Admin strings

**From build-plan:** feature 23a
**Status:** verified

## Goal

Make every ACF Schema Guard Admin menu and screen label discoverable by standard
WordPress translation tooling, load the plugin text domain reliably, and commit
an English POT template for later language catalogues.

## Delivered

- Replaced dynamic screen-title and menu-label translation with literal
  `acf-schema-guard` strings at the Admin definition boundary.
- Added the plugin `Domain Path`, text-domain loading, and
  `languages/acf-schema-guard.pot`.
- Documented the POT regeneration command in the plugin README.
- Added `translation-contract-assertions.php` to prevent a return to
  variable-based screen-label translation.

## Verification

- `php -l wp-content/plugins/acf-schema-guard/acf-schema-guard.php` passed.
- `php -l wp-content/plugins/acf-schema-guard/includes/admin/class-admin-controller.php` passed.
- `php -l wp-content/plugins/acf-schema-guard/tests/translation-contract-assertions.php` passed.
- `sh wp-content/plugins/acf-schema-guard/tests/run-assertions.sh` passed.

## Findings

### 23a/F-01 [P2] closed - Dynamic screen labels cannot be extracted for translation

**File:** `wp-content/plugins/acf-schema-guard/includes/admin/class-admin-controller.php:143`
**Found:** 2026-09-10 by /audit (scope: product architecture and release-readiness; lens: quality)
**Resolution:** 2026-09-14 - screen labels now use literal translation calls and
the focused audit confirmed no dynamic title or menu-label translation calls remain.
