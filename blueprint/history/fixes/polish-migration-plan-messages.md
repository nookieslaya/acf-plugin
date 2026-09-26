# Fix Polish migration-plan messages

**Type:** Fix

**Status:** verified

## The problem

After a Pro migration plan was reviewed, parts of the candidate-selection UI remained in English. The Changes page could also display a conflicts notice for a direct-meta rename with no actual conflicts.

## The fix

The post-review migration controls are translatable in Polish, including their plural forms. The conflicts notice now also requires a positive conflict count.

## Completed work

- [x] Made reviewed-plan controls and the conflict notice state-aware and translatable.
- [x] Refreshed the plugin translation catalogues and compiled the Polish runtime catalogue.

## Verification

- `php -l wp-content/plugins/acf-schema-guard/includes/admin/class-admin-controller.php`
- `sh wp-content/plugins/acf-schema-guard/tests/run-assertions.sh`
- Local WordPress `pl_PL` lookup confirmed the reviewed-plan copy, record label, confirmation text, and copy button in Polish.

## Manual check

Open the `ASG 29c Migration Demo` rename in Changes after a reviewed migration plan. Candidate-selection controls are Polish and a zero-conflict plan has no conflicts notice.
