# Release checklist

## Package integrity

- Run `sh wp-content/plugins/acf-schema-guard/tests/run-assertions.sh`.
- Build a fresh ZIP with `sh wp-content/plugins/acf-schema-guard/scripts/build-release-package.sh`.
- Run `unzip -t wp-content/plugins/acf-schema-guard/dist/acf-schema-guard-1.0.0.zip`.
- Inspect the archive list. It must contain one `acf-schema-guard/` directory,
  `acf-schema-guard.php`, `readme.txt`, `LICENSE`, `includes/`, `assets/css/`,
  and `languages/`. It must not contain `tests/`, `docs/`, `scripts/`,
  `assets/wordpress-org/`, `dist/`, `readme.md`, `.git`, or files from the wider
  WordPress installation.
- Confirm that the version in the plugin header, `readme.txt`, and
  `CHANGELOG.md` is `1.0.0`.

## Fresh-install walkthrough

1. Use a separate disposable WordPress site or a clean local database.
2. Install the generated ZIP through **Plugins → Add New → Upload Plugin**.
3. Activate it with ACF inactive. Confirm that WordPress remains usable and the
   plugin presents its dependency guidance without a fatal error.
4. Activate ACF Free, then open **ACF Schema Guard → Overview**. Confirm that
   the Admin workspace loads and no PHP warnings are visible.
5. If available, repeat with ACF PRO. The core workflow must remain available
   in both editions.
6. Deactivate and delete the plugin through WordPress Admin. Confirm it does
   not delete ACF groups, post meta, or content. Its snapshot table may remain
   intentionally for data safety.

## Workflow and publishing review

- Capture a baseline, change ACF, and review the live comparison in Changes
  without creating a current snapshot.
- Confirm Code Usage, source health, and JSON/Markdown report export.
- Follow `docs/end-to-end-verification.md` and record the tested versions.
- Follow `docs/manual-release-test-pl.md` for the complete Polish Free, Pro,
  Solo Mode, report, and responsive-Admin walkthrough.
- Review `readme.md`, `docs/compatibility.md`, and CI examples.
- Update the public product description for the release: plugin header,
  `readme.txt` **Description**, `readme.md`, `CHANGELOG.md`, and affected
  English and Polish user documentation. Confirm that edition wording and
  available features match the packaged version; do not leave planned features
  described as already shipped.
- Review `docs/wordpress-org-publishing.md`, then copy only the staged assets
  to the separate WordPress.org assets repository after approval.
- Add the release date to the changelog immediately before packaging.
