# Release checklist

- Run `sh wp-content/plugins/acf-schema-guard/tests/run-assertions.sh`.
- Test the plugin with WordPress 6.4+ and PHP 8.1, 8.2, and 8.3.
- Test with ACF Free and ACF PRO.
- Capture a baseline, change ACF, capture current schema, and review Changes.
- Confirm Code Usage, source health, and JSON/Markdown report export.
- Follow `docs/end-to-end-verification.md` and record the tested versions.
- Review `readme.md`, `docs/compatibility.md`, and CI examples.
- Add the release version and date to the changelog before packaging.
