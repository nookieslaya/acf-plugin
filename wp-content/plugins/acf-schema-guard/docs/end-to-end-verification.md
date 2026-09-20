# End-to-end verification

Run this once in a local WordPress site before a release. It verifies the
complete user workflow without modifying ACF values or WordPress content.

1. Activate ACF Schema Guard and confirm that **ACF Schema Guard → Overview**
   opens without a PHP warning.
2. In **History**, capture a known-good schema, request review with a note,
   approve it with a decision note, then set that approved snapshot as the
   baseline. Confirm that an unreviewed snapshot instead says it requires an
   approved review.
3. Make one reversible ACF change, such as changing a text field to textarea.
4. Open **Changes** without capturing another snapshot. Confirm that the
   baseline is compared automatically with the current live schema, the field
   name/type transition is readable, and the expected severity is shown.
5. Open **Code Usage**. Confirm that field references can be expanded and that
   the displayed snippet and line number identify the PHP call site.
   Add or remove one literal ACF call in a configured root, refresh the page,
   and confirm the result changes without creating a schema snapshot.
6. Open **Source Health**. Confirm that database and Local JSON status is
   shown for each field group, without offering a destructive automatic sync.
7. Export one JSON and one Markdown code-usage report with WP-CLI. Confirm that
   a second export to the same path is refused unless `--force` is used.
8. Restore the original ACF field definition and refresh Changes. Confirm that
   the baseline comparison returns to no findings.
9. After deliberately accepting the schema contract for the repository, export
   the Git baseline to a tracked path. Confirm that a second export is refused
   without `--force`, then review its diff before committing it:

   ```sh
   wp acf-schema-guard baseline export path/to/acf-schema-baseline.json
   wp acf-schema-guard baseline export path/to/acf-schema-baseline.json --force
   ```

Record the WordPress, PHP, ACF, and ACF PRO (if installed) versions beside the
release checklist when this has passed.
