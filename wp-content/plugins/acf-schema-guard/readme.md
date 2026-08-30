# ACF Schema Guard

ACF Schema Guard is a WordPress plugin for detecting potentially breaking ACF
schema changes before deployment.

## Current scope

The current bootstrap intentionally exposes no WordPress Admin screen, WP-CLI
command, persistence, schema diff, or code scanner. It offers safe, read-only
ACF environment discovery and a normalized runtime schema for the next
development layers.

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

## Development checks

Run PHP syntax validation for the plugin files:

```sh
find wp-content/plugins/acf-schema-guard -name '*.php' -type f -exec php -l {} \;
php wp-content/plugins/acf-schema-guard/tests/unavailable-acf-assertion.php
php wp-content/plugins/acf-schema-guard/tests/normalized-schema-assertions.php
php wp-content/plugins/acf-schema-guard/tests/snapshot-persistence-assertions.php
```
