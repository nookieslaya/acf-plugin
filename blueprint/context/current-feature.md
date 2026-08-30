# Feature: Normalized schema model

**From build-plan:** feature 3a
**Status:** in progress

## Goal

Create a deterministic, read-only normalized schema model from the ACF runtime.
It must support field groups registered in PHP, loaded from Local JSON, or stored
in WordPress. The result must preserve the ACF properties that later diff and
risk rules need, including nested structures, while removing runtime-only noise.
It must not persist data, create database tables, produce diffs, or add UI and
CLI behavior.

## In scope

- Immutable `NormalizedSchema`, `NormalizedFieldGroup`, and `NormalizedField`
  contracts under a plugin-owned schema namespace.
- A versioned canonical array representation (`schema_version: 1`) with stable
  ordering, suitable for later JSON serialization and hashing.
- A read-only ACF schema-source interface and an ACF runtime implementation that
  obtains full field-group and field data through public ACF APIs only.
- PHP-defined field groups registered through `acf_add_local_field_group()` and
  database-defined field groups are handled through the same ACF runtime API;
  Local JSON is an optional source, not a prerequisite.
- Recursive normalization of top-level fields, repeater and group subfields, and
  Flexible Content layouts and their nested fields.
- Preservation of generic semantic field properties (`key`, `name`, `label`,
  `type`, `required`, `instructions`, `default_value`, `conditional_logic`) and
  type-specific settings in a canonical `settings` map.
- Explicit removal of volatile ACF runtime properties such as database IDs,
  parent IDs, field input prefix, and value state.
- Stable sorting of groups, fields, layouts, map keys, and list values where
  list order is not semantically significant.
- Safe empty output when ACF is unavailable or has no field groups.

## Out of scope

- Snapshot database tables, migrations, checksum generation, serialization to
  storage, retention policy, comparison, risk classification, code scanning,
  Admin screens, WP-CLI, REST routes, or settings.
- Direct parsing of Local JSON files, requiring an `acf-json/` directory, or use
  of ACF internal stores and classes.
- Mapping every field type into dedicated PHP classes. Unknown and custom field
  types remain supported by the generic canonical `settings` map.
- Changing the existing shallow `AcfEnvironmentProvider` contract.

## Build loop

Implement one small reviewed step at a time. Each step must leave the plugin
safe when ACF is inactive and must not introduce persistence or user-facing UI.

## Build steps

- [x] **Step 1 - Normalized schema contracts** - add immutable schema,
  field-group, and field value objects with canonical array output and explicit
  schema version. *Done when:* equivalent objects produce the same ordered
  canonical structure without depending on WordPress or ACF globals.
- [x] **Step 2 - ACF full-schema source boundary** - add an interface and a
  read-only ACF implementation that safely obtains field groups and their full
  fields through `acf_get_field_groups()` and `acf_get_fields()`. *Done when:*
  unavailable ACF returns no groups and the source exposes no raw data outside
  the normalizer boundary.
- [x] **Step 3 - Recursive canonical normalizer** - transform full ACF groups
  into normalized contracts; include repeaters, groups, Flexible Content
  layouts, conditional logic, and generic type settings while excluding volatile
  runtime keys. *Done when:* equivalent input ordering produces identical
  canonical arrays and nested test-theme structures are retained.
- [ ] **Step 4 - Plugin seam and deterministic verification** - expose the
  normalizer through the existing plugin service for later snapshot capture,
  document the normalized contract, and run focused isolated PHP assertions plus
  `php -l`. *Done when:* a later feature can obtain one complete normalized
  schema without database writes or ACF-internal dependencies.

## Files / areas

- `wp-content/plugins/acf-schema-guard/includes/schema/`
- `wp-content/plugins/acf-schema-guard/includes/acf/`
- `wp-content/plugins/acf-schema-guard/includes/class-plugin.php`
- `wp-content/plugins/acf-schema-guard/readme.md`

## Data / contracts

The canonical representation is load-bearing for features 3b, 3c, and 4.

### `NormalizedSchema`

- `schema_version` (int) - fixed at `1` for this contract.
- `field_groups` (`NormalizedFieldGroup[]`) - sorted by group key.

### `NormalizedFieldGroup`

- `key`, `title`, `active`, `location`, and `fields`.
- `location` retains ACF's OR-of-AND structure; order within an AND rule group is
  canonicalized by `param`, `operator`, then `value`.

### `NormalizedField`

- generic fields: `key`, `name`, `label`, `type`, `required`, `instructions`,
  `default_value`, `conditional_logic`.
- `settings` - recursively key-sorted values from the remaining semantic ACF
  configuration after removing known structural and volatile keys.
- `sub_fields` - recursively normalized children for nested field types.
- `layouts` - sorted Flexible Content layouts, each with `key`, `name`, `label`,
  `display`, `settings`, and normalized `sub_fields`.

### ACF full-schema source

- `field_groups()` returns only full ACF group arrays required by the normalizer.
- Its runtime implementation calls public `acf_get_field_groups()` and
  `acf_get_fields()` only after availability checks.
- It treats the ACF runtime as the source of truth. The normalizer must not infer
  `database` versus `php` provenance when ACF's public API does not expose it.
- The normalizer is the only consumer of its raw arrays; later domain layers
  receive normalized contracts exclusively.

## Testing

No project test runner is configured. Add focused isolated PHP assertion scripts
without adding a test framework: verify stable output after reordering maps and
groups, nested repeater and Flexible Content retention, volatile-key exclusion,
and the unavailable-ACF empty schema path. Run `php -l` for every plugin PHP
file. With ACF PRO active, manually inspect normalized schemas from both the test
theme's Local JSON field groups and a temporary PHP-registered field group.

## Notes for the AI

- Use PHP 7.4-compatible syntax and public ACF API only.
- Do not require an `acf-json/` directory. ACF runtime data is the normalizer's
  input regardless of whether field groups originated in PHP, Local JSON, or the
  database.
- Do not make a field-type whitelist. Generic canonical settings keep custom ACF
  field types forward-compatible, while known runtime keys are removed by a
  narrow, documented exclusion list.
- Preserve user-authored list order where it carries semantic meaning, including
  repeater subfield order and Flexible Content layout field order. Canonicalize
  only maps and logically unordered location and conditional rule groups.
- Do not add snapshot IDs, timestamps, hashes, or storage concerns. Feature 3b
  owns persistence and feature 3c owns capture orchestration.
