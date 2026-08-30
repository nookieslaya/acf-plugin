# Feature: Plugin bootstrap and ACF integration boundary

**From build-plan:** feature 2
**Status:** verified

## Goal

Create the smallest production-quality ACF Schema Guard plugin shell and a
read-only integration boundary that can safely report the available ACF runtime,
field groups, Local JSON load paths, and Local JSON files. This establishes a
stable input contract for the later schema normalization feature without storing
snapshots, classifying changes, scanning code, or adding an Admin UI.

## In scope

- A plugin at `wp-content/plugins/acf-schema-guard/` with standard WordPress
  metadata, direct-access protection, lifecycle-safe initialization, and no
  external dependencies.
- A small namespaced PHP structure with one composition root and ACF-specific
  classes isolated under `includes/acf/`.
- A read-only `AcfEnvironment` value object reporting availability, ACF version,
  PRO availability, configured Local JSON paths, and field-group descriptors.
- Field-group discovery through public ACF APIs when present:
  `acf_get_field_groups()`, `acf_get_local_json_files()`, and
  `acf_get_setting( 'load_json' )`.
- Correlation of a Local JSON file to a field group by its stable ACF group key.
- Safe behavior with ACF missing, inactive, partially loaded, or without any
  field groups: no fatal error, no writes, and empty discovery results.
- A narrow developer-facing action hook for later consumers to obtain the
  initialized plugin service without introducing a public screen or CLI command.

## Out of scope

- Schema normalization, snapshot persistence, diffs, risk rules, scanner logic,
  admin screens, WP-CLI, settings, REST routes, AJAX, database tables, or cron.
- Reading field internals beyond the minimal descriptor contract, parsing JSON
  files directly, or treating Local JSON as the sole source of truth.
- Automatic activation, installation, licensing, or configuration of ACF/ACF
  PRO, and any user-facing admin notice.
- Composer, a dependency-injection container, a framework, or a custom autoloader.

## Build loop

Build one small, reviewable step at a time. Each step leaves the plugin safe to
activate, presents a diff, and requires approval before the next step.

## Build steps

- [x] **Step 1 - Plugin shell and lifecycle bootstrap** - add the plugin header,
  `ABSPATH` guard, plugin constants, and a namespaced composition root initialized
  after WordPress plugins load. *Done when:* WordPress recognizes the plugin,
  direct execution exits, and all plugin PHP files pass `php -l` without ACF.
- [x] **Step 2 - Immutable ACF discovery contracts** - add small value objects
  for runtime capability and field-group descriptors, including group key, title,
  active state, source type, and optional Local JSON file path. *Done when:* the
  contract exposes no WordPress globals or raw ACF arrays to later domain code,
  and it is compatible with PHP 7.4 syntax.
- [x] **Step 3 - Safe ACF and Local JSON provider** - implement a read-only
  provider that checks for ACF APIs before calling them, collects configured
  `load_json` paths, gets field groups, and correlates `acf_get_local_json_files()`
  by group key. *Done when:* missing ACF returns a deterministic unavailable
  environment; available ACF returns descriptors without writes or direct JSON
  parsing.
- [x] **Step 4 - Plugin integration seam and static verification** - wire the
  provider into the plugin root through one documented action hook for future
  consumers, add a short developer README, and validate every PHP file with
  `php -l`. *Done when:* later features have one stable access point, while no
  schema engine, persistence, UI, or command is introduced.

## Files / areas

- `wp-content/plugins/acf-schema-guard/acf-schema-guard.php`
- `wp-content/plugins/acf-schema-guard/includes/class-plugin.php`
- `wp-content/plugins/acf-schema-guard/includes/acf/`
- `wp-content/plugins/acf-schema-guard/readme.md`

## Data / contracts

### `AcfEnvironment`

- `is_available` (bool) - ACF APIs required by the provider are callable.
- `is_pro` (bool) - ACF PRO capability is detected without assuming a license.
- `version` (string|null) - loaded ACF version when exposed by ACF.
- `local_json_paths` (string[]) - configured, unique load paths.
- `field_groups` (`FieldGroupDescriptor[]`) - discovery result, possibly empty.

### `FieldGroupDescriptor`

- `key` (string) - stable ACF field-group key.
- `title` (string) - human-readable ACF group title.
- `is_active` (bool) - active state reported by ACF.
- `source` (`runtime` or `local_json`) - `local_json` when ACF maps the group key
  to a Local JSON file.
- `local_json_file` (string|null) - mapped path only; its contents remain owned
  by ACF until the normalization feature.

## Testing

No test runner or Verify command is configured. Verification used `php -l` for
every plugin PHP file and isolated PHP checks for unavailable ACF and the plugin
integration seam. Runtime browser evidence remains a manual quality gate.

## Notes for the AI

- Use PHP 7.4-compatible syntax. This is the conservative floor imposed by the
  installed ACF PRO 6.7.0.2; adding a formal `Requires PHP` plugin header remains
  deferred until the product-wide PHP support policy is decided.
- Do not use ACF internal classes, stores, or file-scanning implementation
  details. The provider may only rely on the public functions named in scope.
- Do not call ACF functions until the provider method executes; this keeps plugin
  activation safe if ACF is inactive or loads later.
- Keep descriptors deliberately shallow. Feature 3 will introduce normalized
  schema data and snapshot storage.
