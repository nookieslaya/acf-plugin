# CI integration

Run the check only after WordPress, the plugin, ACF, a database, and the two
immutable snapshot IDs are available in the job.

```sh
wp acf-schema-guard check "$ACF_SCHEMA_GUARD_BEFORE_ID" "$ACF_SCHEMA_GUARD_AFTER_ID" --fail-on-breaking
```

The command is read-only. It exits non-zero only for `high` or `critical` risks
when `--fail-on-breaking` is supplied. A preceding, explicit job must create or
retrieve the snapshot IDs; this plugin does not guess a baseline in CI.

## Using a template

For GitHub Actions, copy `github-actions.example.yml` to
`.github/workflows/acf-schema-guard.yml` in the repository that runs WordPress.
For GitLab CI, copy the `acf_schema_guard_check` job from
`gitlab-ci.example.yml` into that repository's `.gitlab-ci.yml`. Then replace
the bootstrap comment with the project's real WordPress and database setup, and
provide both snapshot IDs as protected CI variables or from an earlier job.
