# ACF Schema Guard

ACF Schema Guard is a WordPress plugin for detecting potentially breaking ACF
schema changes before deployment.

## Current scope

The plugin offers safe, read-only ACF environment discovery, normalized schemas,
snapshots, change analysis, and PHP ACF usage scanning. Its WordPress Admin menu
is currently an information-only foundation: Overview, Changes, Field Groups,
Code Usage, History, and Settings have no buttons, forms, persistence, or
automatic work. Later features will connect those screens to explicit actions.

## WordPress Admin foundation

Administrators can open the `ACF Schema Guard` menu to see six stable section
screens. Each is protected with the `manage_options` capability. The plugin
loads its small Admin stylesheet only on those screens and does not change data
when they are viewed.

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
