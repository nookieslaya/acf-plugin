# CI integration

Commit an approved baseline JSON file, then run the check after WordPress, the
plugin, ACF, and a database are available in the job.

```sh
wp acf-schema-guard baseline check acf-schema-baseline.json --fail-on-breaking
```

The command is read-only. It exits non-zero only for `high` or `critical` risks
when `--fail-on-breaking` is supplied. The baseline file is part of the checked
out Git revision; CI does not need snapshot IDs or a historical plugin database.

Create or deliberately update the baseline locally after reviewing a known-good
schema change:

```sh
wp acf-schema-guard baseline export acf-schema-baseline.json --force
git add acf-schema-baseline.json
```

## Using a template

For GitHub Actions, copy `github-actions.example.yml` to
`.github/workflows/acf-schema-guard.yml` in the repository that runs WordPress.
For GitLab CI, copy the `acf_schema_guard_check` job from
`gitlab-ci.example.yml` into that repository's `.gitlab-ci.yml`. Then replace
the bootstrap comment with the project's real WordPress and database setup. The
examples expect `acf-schema-baseline.json` at the repository root; change that
path consistently if the project stores it elsewhere.
