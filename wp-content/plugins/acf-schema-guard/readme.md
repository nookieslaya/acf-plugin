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

## Development checks

Run PHP syntax validation for the plugin files:

```sh
find wp-content/plugins/acf-schema-guard -name '*.php' -type f -exec php -l {} \;
php wp-content/plugins/acf-schema-guard/tests/unavailable-acf-assertion.php
php wp-content/plugins/acf-schema-guard/tests/normalized-schema-assertions.php
```
