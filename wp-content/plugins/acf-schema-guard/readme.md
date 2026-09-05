# ACF Schema Guard

ACF Schema Guard is a WordPress plugin for detecting potentially breaking ACF
schema changes before deployment.

## What the plugin does

ACF Schema Guard reads ACF's effective runtime schema, normalizes it, compares
versions, and classifies potentially unsafe changes. It works whether field
groups come from the database, ACF Local JSON, PHP registration, or a mixture of
those sources. It does not modify ACF field definitions, post meta, or content.

The plugin stores its own immutable snapshots in a dedicated WordPress table.
It also supports a portable baseline JSON file that can be reviewed and tracked
in Git for CI checks.

## WordPress Admin workspace

Administrators with `manage_options` can open **ACF Schema Guard** in the
WordPress Admin. The available workflow is:

1. Open **History** and choose **Capture current schema**.
2. Set a known-good snapshot as the approved baseline.
3. Make and save ACF changes, then capture another snapshot.
4. Open **Changes** to compare the approved baseline automatically with the
   newest captured schema.

The Admin baseline is stored as a snapshot ID in WordPress. It is useful for
local review, but is different from the Git baseline file used by CI.

## WP-CLI scan

When WP-CLI loads the plugin, scan explicit PHP source directories for supported
literal ACF field references:

```sh
wp acf-schema-guard scan wp-content/themes/acf-schema-guard-dev
wp acf-schema-guard scan wp-content/themes/acf-schema-guard-dev --format=json
```

Provide at least one readable directory. The command supports `table` (default)
and `json` output. It reports references from `get_field()`, `the_field()`,
`get_sub_field()`, `the_sub_field()`, `have_rows()`, and `get_field_object()`
when their first argument is a literal string. It does not execute or modify the
scanned files, create snapshots, or change WordPress data.

## WP-CLI diff

Compare two stored snapshot IDs without changing them:

```sh
wp acf-schema-guard diff <before-id> <after-id>
wp acf-schema-guard diff <before-id> <after-id> --format=json
```

The table reports schema change kind, node type, path, severity, and rationale.

## WP-CLI check

Use the same two snapshot IDs in CI and fail only for `high` or `critical` risks:

```sh
wp acf-schema-guard check <before-id> <after-id> --fail-on-breaking
```

Without `--fail-on-breaking`, a valid analysis exits successfully. The flag
returns a non-zero exit status after printing the analysis when breaking risks
are found; `safe` and `warning` findings do not fail the command.

## Git baseline and CI workflow

Use the Git baseline when you want a project-wide, reviewable contract that CI
can compare with the schema loaded by the current checkout.

Run all commands below from the WordPress project root: the directory that
contains `wp-config.php`. Use a shell where WP-CLI can load the same WordPress,
PHP, database, ACF, and plugin installation as the site. For Local, the
site-specific shell supplied by Local is the simplest option.

### 1. Create the first approved baseline

After reviewing a known-good ACF schema, export it to a chosen path and confirm
that it matches the current runtime schema:

```sh
wp acf-schema-guard baseline export acf-schema-baseline.json
wp acf-schema-guard baseline check acf-schema-baseline.json --fail-on-breaking
```

The expected result is `Success: No schema changes found.`. Then review and
commit the file:

```sh
git add acf-schema-baseline.json
git commit -m "chore: add ACF schema baseline"
```

If the repository ignores new files at its root, explicitly allow the baseline
in `.gitignore` or use `git add -f acf-schema-baseline.json` for this initial
commit. Once Git tracks the file, ordinary `git add` works for later updates.

### 2. Check a change before merging

Make an ACF change in a branch, ensure its Local JSON or PHP definition is
available to WordPress, then run:

```sh
wp acf-schema-guard baseline check acf-schema-baseline.json --fail-on-breaking
```

The command always prints findings. With `--fail-on-breaking`, it returns a
non-zero exit status only for `high` and `critical` findings, which lets a CI job
block the pull request. Use `--format=json` when another tool needs the complete
machine-readable analysis.

| Severity | Meaning in the current policy | CI result with `--fail-on-breaking` |
| --- | --- | --- |
| `safe` | A schema node was added. | Success |
| `warning` | A non-breaking schema node change was detected. | Success |
| `high` | A field type changed. | Failure |
| `critical` | A field group or field was removed. | Failure |

An exit-code failure is a review signal, not an automatic migration or rollback.
Inspect the output and any theme/plugin code references before deciding what to
do.

### 3. Approve an intentional schema change

Only after reviewing the ACF change and updating dependent code, replace the
baseline deliberately:

```sh
wp acf-schema-guard baseline export acf-schema-baseline.json --force
wp acf-schema-guard baseline check acf-schema-baseline.json --fail-on-breaking
git add acf-schema-baseline.json
git commit -m "chore: update ACF schema baseline"
```

`--force` is required because export will not overwrite an existing baseline by
accident. Never update the baseline merely to silence a CI failure; the baseline
commit is the explicit approval record for the new schema contract.

### 4. Run the same check in CI

The CI job must first boot the project's real WordPress environment, database,
ACF, and this plugin. It then runs the same committed-file check:

```sh
wp acf-schema-guard baseline check acf-schema-baseline.json --fail-on-breaking
```

See `docs/ci.md` and the GitHub Actions and GitLab CI examples for starter job
definitions. They are templates only: copying one into a repository does not
itself provision WordPress, a database, or ACF in the CI runner.

### Git baseline and Admin baseline compared

| Use case | Git baseline file | Admin baseline snapshot |
| --- | --- | --- |
| Stored in | A committed JSON file | The plugin snapshot table and a WordPress option |
| Primary use | Pull-request and CI checks | Interactive local/admin review |
| Needs historical plugin database in CI | No | Yes |
| Created with | `baseline export` | **History → Capture current schema** |
| Updated deliberately with | `baseline export --force` and a Git commit | **Set as baseline** in History |

Both paths are read-only with respect to ACF definitions and content. Baseline
export writes only the explicitly named JSON file; snapshot capture writes only
the plugin's own snapshot table.

## CI templates

Read `docs/ci.md` for GitHub Actions and GitLab CI example templates. They are
documentation only until copied into the repository that owns the CI pipeline.

## Integration seam

After all WordPress plugins load, ACF Schema Guard fires:

```php
do_action( 'acf_schema_guard/booted', $plugin );
```

Future internal integrations can obtain the plugin service through that action
and call `$plugin->acf_environment()`. The returned environment exposes ACF
availability, PRO capability, version, configured Local JSON load paths, and
field-group descriptors.

Call `$plugin->normalized_schema()->to_array()` to obtain the complete current
schema. Its `schema_version` is `1`; field groups, top-level fields, layouts,
map keys, and logical rule groups have deterministic ordering. Nested repeater
and Flexible Content layout fields retain their configured order. Runtime-only
data such as database IDs, parents, prefixes, field values, and menu order is
excluded. This works with fields registered in PHP, Local JSON, or the database
because ACF's public runtime APIs are the source of truth.

The providers use only public ACF APIs and perform no writes. They remain safe
when ACF is inactive or unavailable.

## Snapshot storage

Snapshots are immutable records in the dedicated
`{$wpdb->prefix}acf_schema_guard_snapshots` table. Plugin activation installs
or upgrades the table. It stores a UUID, caller-provided source ID, schema
version, canonical JSON schema, and UTC creation time. No snapshot data is kept
in `wp_options`.

Future capture code can obtain the append-only repository with
`$plugin->snapshot_repository()`. It provides `insert()`, `find()`, and
`latest_for_source()` only; it does not update or delete snapshots.

To capture explicitly, call `$plugin->capture_snapshot( 'acf-runtime' )` after
the plugin is loaded. It writes one new snapshot only when ACF is available;
plugin boot and activation never trigger capture.

## Development checks

Run PHP syntax validation for the plugin files:

```sh
find wp-content/plugins/acf-schema-guard -name '*.php' -type f -exec php -l {} \;
php wp-content/plugins/acf-schema-guard/tests/unavailable-acf-assertion.php
php wp-content/plugins/acf-schema-guard/tests/normalized-schema-assertions.php
php wp-content/plugins/acf-schema-guard/tests/snapshot-persistence-assertions.php
```
