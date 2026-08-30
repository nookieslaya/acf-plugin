# ACF Schema Guard - Project Overview

<!-- blueprint:source-hash d8bbdf438e201ec5f367cf09af46c7ddf3b39b61a7cfd48f56c55435297be98e -->

> A WordPress plugin that identifies potentially breaking ACF schema changes before they reach production.

## Problem

ACF field changes can silently break WordPress themes and plugins. Developers need to compare schema versions, understand the risk of each difference, and find code that still uses fields that were removed or renamed before deployment.

## Users

- WordPress developers and freelancers maintaining ACF-based sites.
- Software houses and agencies collaborating through ACF Local JSON and Git.
- Development teams that want a reliable CI decision on ACF schema safety.

## Features

1. **ACF development test theme** - a realistic, minimal environment for ACF, Local JSON, nested fields, and code-usage examples.
2. **Plugin bootstrap and ACF integration boundary** - safe ACF and ACF PRO discovery with support for field groups and Local JSON sources.
3. **Schema normalization and snapshots** - stable representations of field groups and stored versions for comparison.
4. **Schema diff and extensible risk classification** - change detection with safe, warning, high, and critical risk results.
5. **Code-usage scanner architecture** - pluggable language strategies and a durable usage-reference model.
6. **PHP ACF usage scanner** - supported PHP call-site detection linked to changed fields.
7. **WordPress Admin foundation** - the minimal developer-facing plugin menu and named section screens.
8. **WP-CLI analysis commands** - scan, diff, and blocking CI checks.
9. **CI integration guidance and verification** - reusable GitHub Actions and GitLab CI examples based on the CLI.

## Data model

### Normalized schema

- `source_id` (string) - identifier for the ACF or Local JSON source.
- `field_groups` (array) - groups containing title, key, location, and fields.
- `fields` (tree) - field key, name, type, relevant settings, and nested repeater, group, clone, and flexible-content structures.

### Schema snapshot

- `id` (string) - immutable snapshot identifier.
- `schema` (normalized schema) - the comparable source data.
- `created_at` (datetime) - creation time.
- `source_id` (string) - source represented by the snapshot.

### Schema finding

- `change_type` (string) - detected schema change category.
- `severity` (enum) - safe, warning, high, or critical.
- `before` and `after` (schema node or null) - the compared nodes.
- `rationale` (string) - explainable reason for the risk classification.

### Code usage reference

- `field_name` (string) - referenced ACF field name.
- `strategy` (string) - scanner implementation that reported the usage.
- `path` (string) and `line` (integer) - source location.
- `expression` (string) - supported ACF API expression at that location.

### Plugin settings

- `scanner_configuration` (array) - enabled strategies and source roots.
- `risk_rule_policy` (array) - future user policy for extensible rules.

## Tech stack

- **WordPress 7.1** - local runtime and future plugin host.
- **PHP** - plugin and classic test-theme language.
- **ACF or ACF PRO** - optional field-group runtime dependency.
- **ACF Local JSON** - primary schema source for development tests.
- **WP-CLI** - local and CI entry point.
- **Git, GitHub Actions, GitLab CI** - source control and later automation.

## Monetization

> TODO - licensing and monetization are not decided.

## UI/UX

- WordPress Admin: developer-first ACF Schema Guard menu with Overview, Changes, Field Groups, Code Usage, History, and Settings.
- Development theme front end: minimal, accessible classic templates that make ACF output and scanner references easy to inspect.

## Deployment

Local WordPress is the initial execution environment. Future CI will run WP-CLI checks in GitHub Actions and GitLab CI.

> TODO - decide distribution, release process, CI runtime, PHP compatibility matrix, and public documentation site.

## Open questions

- The minimum supported PHP version has not been decided.
- ACF is not currently installed or active in this local instance; the theme can be built with guarded calls and Local JSON, but runtime ACF verification needs a later available ACF or ACF PRO installation.
