# ACF Schema Guard - Project Overview

<!-- blueprint:source-hash 33c849b7676986df8f8602b53b6b19d5ecc6cdfbf88141a3d7656ba9763ac0ae -->

> A WordPress plugin that identifies potentially breaking ACF schema changes before they reach production.

## Problem

Changing an Advanced Custom Fields schema can silently break WordPress themes or
plugins, often only becoming visible after deployment. ACF Schema Guard captures
schema versions, compares them, classifies risk, and is designed to identify
code still referring to removed or renamed fields before release.

## Users

- WordPress developers and freelancers maintaining ACF-based sites.
- Software houses and agencies collaborating through ACF Local JSON and Git.
- Development teams that need a CI-friendly answer to whether the latest ACF
  changes are safe.

## Features

1. **ACF development test theme** - a realistic local environment with ACF
   Local JSON and breaking-change scenarios.
2. **Plugin bootstrap and ACF integration boundary** - safely discovers ACF,
   ACF PRO, field groups, and Local JSON sources.
3. **Schema normalization and snapshots** - produces deterministic schema
   contracts, persists immutable snapshots, and captures them explicitly.
4. **Schema diff and extensible risk classification** - reports deterministic
   schema changes with safe, warning, high, and critical classifications.
5. **Code-usage scanner architecture** - provides replaceable language
   strategies and a durable reference model.
6. **PHP ACF usage scanner** - finds supported PHP ACF call sites and connects
   them to changed fields.
7. **WordPress Admin foundation** - supplies the developer-facing Overview,
   Changes, Field Groups, Code Usage, History, and Settings screens.
8. **WP-CLI analysis commands** - scan PHP usage, diff stored snapshots, and
   fail CI checks for breaking changes.
9. **CI integration guidance and verification** - provides GitHub Actions and
   GitLab CI examples around the WP-CLI check command.
10. **Admin snapshot workspace** - lets administrators capture schemas, review
    snapshot history, and compare stored snapshots in the WordPress Admin.
11. **Comprehensive schema change explainer** - will add core, setting, and
    nested-structure details, shared Admin/CLI output, and an accessible visual
    severity system for ACF Schema Guard Admin change views only.
12. **Baseline comparison workflow** - provides an approved Admin baseline,
    automatic current-schema comparison, and a versioned Git/CI baseline.

## Data model

### Normalized schema

- `source_id` (string) - identifier for the ACF or Local JSON source.
- `field_groups` (array) - groups with title, key, location, and fields.
- `fields` (tree) - field key, name, type, relevant settings, and nested
  repeater, group, clone, and flexible-content structures.

### Schema snapshot

- `id` (UUID string) - immutable snapshot identifier.
- `source_id` (string) - source represented by the snapshot.
- `schema_version` (integer) - version of the normalized-schema format.
- `schema` (normalized schema) - comparable source data.
- `created_at` (datetime) - capture time.
- persistence (dedicated WordPress table) - snapshot records are not stored in
  `wp_options`.

### Schema finding

- `change_type` (string) - detected schema-change category.
- `severity` (enum) - safe, warning, high, or critical.
- `before` and `after` (schema node or null) - the compared nodes.
- `rationale` (string) - explainable risk rationale.

### Code usage reference

- `field_name` (string) - referenced ACF field name.
- `strategy` (string) - scanner implementation that reported the reference.
- `path` (string) and `line` (integer) - source location.
- `expression` (string) - supported ACF API expression at that location.

### Plugin settings

- `scanner_configuration` (array) - enabled strategies and source roots.
- `risk_rule_policy` (array) - future policy for extensible rules.

## Tech stack

- **WordPress** - local plugin runtime and future plugin host.
- **PHP** - plugin and classic test-theme language.
- **ACF or ACF PRO** - field-group runtime dependency when available.
- **ACF Local JSON** - primary schema source for development tests.
- **WP-CLI** - local and CI execution path.
- **Dedicated WordPress table** - immutable normalized snapshot storage.
- **Git, GitHub Actions, GitLab CI** - source control and CI integration.

## Monetization

> TODO - licensing and monetization are not decided for v1.

## UI/UX

- WordPress Admin - developer-first ACF Schema Guard menu with Overview,
  Changes, Field Groups, Code Usage, History, and Settings.
- ACF Schema Guard change views use severity colours as a scanning aid alongside
  text labels and a legend, so colour is never the sole risk signal; the plugin
  does not restyle global WordPress Admin, ACF editor, or other plugins.
- Development theme front end - minimal, accessible classic templates for
  inspecting ACF output and scanner references.

## Deployment

The plugin is developed and tested in a local WordPress installation. CI uses
WP-CLI checks through GitHub Actions or GitLab CI.

> TODO - decide the distribution channel, release process, CI runtime, PHP
> compatibility matrix, and public documentation site.

## Open questions

- The minimum supported PHP version has not been decided.
- Distribution, release process, CI runtime, PHP matrix, and documentation site
  are not yet decided.
