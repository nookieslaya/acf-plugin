# ACF Schema Guard

ACF Schema Guard is a WordPress plugin for detecting potentially breaking ACF
schema changes before deployment.

## Current scope

The current bootstrap intentionally exposes no WordPress Admin screen, WP-CLI
command, persistence, schema diff, or code scanner. It only offers a safe,
read-only description of the loaded ACF environment for the next development
layers.

## Integration seam

After all WordPress plugins load, ACF Schema Guard fires:

```php
do_action( 'acf_schema_guard/booted', $plugin );
```

Future internal integrations can obtain the plugin service through that action
and call `$plugin->acf_environment()`. The returned environment exposes ACF
availability, PRO capability, version, configured Local JSON load paths, and
field-group descriptors.

The provider uses only public ACF APIs and performs no writes. It remains safe
when ACF is inactive or unavailable.

## Development checks

Run PHP syntax validation for the plugin files:

```sh
find wp-content/plugins/acf-schema-guard -name '*.php' -type f -exec php -l {} \;
```
