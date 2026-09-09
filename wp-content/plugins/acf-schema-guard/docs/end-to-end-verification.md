# End-to-end verification

Run this once in a local WordPress site before a release. It verifies the
complete user workflow without modifying ACF values or WordPress content.

1. Activate ACF Schema Guard and confirm that **ACF Schema Guard → Overview**
   opens without a PHP warning.
2. In **History**, capture a known-good schema and set that snapshot as the
   approved baseline.
3. Make one reversible ACF change, such as changing a text field to textarea,
   then capture the current schema.
4. Open **Changes**. Confirm that the baseline is compared automatically with
   the newest capture, the field name/type transition is readable, and the
   expected severity is shown.
5. Open **Code Usage**. Confirm that field references can be expanded and that
   the displayed snippet and line number identify the PHP call site.
6. Open **Source Health**. Confirm that database and Local JSON status is
   shown for each field group, without offering a destructive automatic sync.
7. Export one JSON and one Markdown code-usage report with WP-CLI. Confirm that
   a second export to the same path is refused unless `--force` is used.
8. Restore the original ACF field definition, capture once more, and confirm
   that the baseline comparison returns to no findings.

Record the WordPress, PHP, ACF, and ACF PRO (if installed) versions beside the
release checklist when this has passed.
