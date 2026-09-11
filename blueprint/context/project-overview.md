# ACF Schema Guard - Project Overview

<!-- blueprint:source-hash ed90df4ec400bc36217dbc50f614e065172008d4881882e149ff92b1e970fd5f -->

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
11. **Comprehensive schema change explainer** - adds core details first, then
    rules/settings and nested structures, followed by shared Admin/CLI output
    and an accessible visual severity system for ACF Schema Guard Admin change
    views only.
12. **Baseline comparison workflow** - provides an approved Admin baseline,
    automatic current-schema comparison, and a versioned Git/CI baseline.
13. **ACF Local JSON source health** - identifies whether field groups are
    aligned between the WordPress database and ACF Local JSON, then presents
    the findings in the Admin workspace, including future direction-aware sync
    guidance.
14. **ACF code-impact workspace** - connects ACF changes with supported PHP
    references, configurable scan roots, and exportable reports.
15. **Stabilization and release readiness** - establishes quality, compatibility,
    documentation, and end-to-end release evidence.
16. **Free and Pro product foundation** - defines edition boundaries before
    license-aware advanced team workflows are introduced.
17. **Live schema and code workflow** - removes unnecessary manual captures by
    comparing current schema and current PHP files at analysis time.
18. **Admin UX/UI refinement** - turns Overview into an actionable decision
    dashboard and unifies the complete plugin workspace around responsive,
    ACF-Pro-inspired controls, tables, states, and guidance.
19. **Data impact and dynamic code references** - makes incomplete PHP evidence
    explicit and reports safe, bounded evidence of stored records affected by a
    risky field-schema change.
20. **Nested ACF data impact** - extends safe stored-data evidence to reliable
    Group, Repeater, and Flexible Content storage patterns, then distinguishes
    direct, nested, and unknown evidence in Changes.
21. **Potentially unused ACF fields** - provides review-only coverage signals
    for current fields without treating a lack of literal references as a safe
    deletion decision.

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

### Dynamic code-reference finding

- `strategy` (string) - scanner implementation that detected the call.
- `path` (string) and `line` (integer) - source location.
- `expression` (string) - supported ACF call with a non-literal first argument.
- `review_status` (enum) - `manual_review_required`; no field name is inferred.

### Data-impact summary

- `field_name` (string) - affected field name from a removed or renamed schema
  finding.
- `record_count` (integer) - bounded count of records with an exact matching
  WordPress meta key.
- `records` (array) - limited safe identifiers such as post ID, post type, and
  title; never stores or renders field values.

### Nested data-impact evidence

- `field_path` (array of strings) - schema-derived ancestry from the affected
  field to its field group.
- `storage_pattern` (string) - a bounded WordPress meta-key pattern derived
  from a supported ACF Group, Repeater, or Flexible Content structure.
- `confidence` (enum) - `direct`, `nested`, or `unknown`; `unknown` is retained
  whenever ACF storage cannot be proven safely from the normalized schema.
- `record_count` and `records` - the same bounded, identifier-only evidence as
  direct impact; field values are never read, stored, rendered, or migrated.

### Potentially unused field signal

- `field_key` and `field_name` (strings) - current normalized ACF field
  identity.
- `literal_reference_count` (integer) - current scanner evidence from configured
  roots.
- `dynamic_reference_present` (boolean) - indicates that a supported dynamic
  call prevents a confident unused conclusion.
- `review_state` (enum) - a review signal only, never an automatic deletion or
  migration recommendation.

### Plugin settings

- `scanner_configuration` (array) - enabled strategies and source roots.
- `risk_rule_policy` (array) - future policy for extensible rules.

### Source-health finding

- `field_group_key` (string) - ACF field-group key used to match database and
  Local JSON representations.
- `status` (enum) - `aligned`, `database_only`, `json_only`, or `divergent`.
- `database_group` and `json_group` (field-group schema or null) - source
  representations used to determine the status.

## Tech stack

- **WordPress** - local plugin runtime and future plugin host.
- **PHP** - plugin and classic test-theme language.
- **ACF or ACF PRO** - ACF Free is supported; ACF PRO is optional.
- **ACF Local JSON** - primary schema source for development tests.
- **WP-CLI** - local and CI execution path.
- **Dedicated WordPress table** - immutable normalized snapshot storage.
- **Git, GitHub Actions, GitLab CI** - source control and CI integration.

## Monetization

Free includes core schema safety. Pro will add advanced team workflows without
blocking access to stored data or Free safety features when a license is absent.

## UI/UX

- WordPress Admin - developer-first ACF Schema Guard menu with an actionable
  Overview, Changes, Field Groups, Code Usage, History, and Settings.
- The complete ACF Schema Guard workspace is cohesive and responsive, drawing
  visual inspiration from ACF Pro's restrained tables, white surfaces, subtle
  gray structure, and blue actions, without restyling global WordPress Admin,
  ACF editor, or other plugins.
- Severity colours are a scanning aid alongside text labels and a legend, so
  colour is never the sole risk signal.
- Dynamic PHP calls are clearly marked as incomplete evidence requiring manual
  review, while data-impact views expose counts and safe record identifiers only.
- Nested-data and unused-field views preserve uncertainty explicitly: the plugin
  reports only schema-derived storage patterns and literal code evidence it can
  substantiate.
- Development theme front end - minimal, accessible classic templates for
  inspecting ACF output and scanner references.

## Deployment

The plugin is developed and tested in a local WordPress installation. CI uses
WP-CLI checks through GitHub Actions or GitLab CI.

> TODO - decide the distribution channel, release process, CI runtime, PHP
> compatibility matrix, and public documentation site.

## Open questions

- Initial compatibility target: WordPress 6.4+, PHP 8.1+, and current supported
  ACF or ACF PRO releases.
- Distribution, release process, CI runtime, and documentation site remain open.
