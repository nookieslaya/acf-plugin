# ACF Schema Guard - Project Plan

## 1. Problem - What problem are we solving?

Changing an Advanced Custom Fields schema can break WordPress themes or plugins,
yet developers frequently discover the failure only after deployment. ACF Schema
Guard will snapshot ACF field-group schemas, compare versions, classify the risk
of each change, and eventually identify code that still references removed or
renamed fields.

## 2. Users - Who are we focusing on?

- WordPress developers and freelancers maintaining ACF-based sites.
- Software houses and agencies working in teams with ACF Local JSON and Git.
- Development teams that need a CI-friendly answer to: "Are the latest ACF
  changes safe?"

## 3. Features - What does the MVP need?

- A development theme with realistic ACF test cases and Local JSON support.
- A plugin integration layer for ACF and ACF PRO field groups and Local JSON.
- A schema engine for normalization, snapshots, diffs, and extensible risk rules.
- A code-usage scanning architecture that can later support PHP, Blade, Twig,
  Timber, Sage, custom ACF wrappers, and AST-based strategies.
- A minimal WordPress Admin foundation for Overview, Changes, Field Groups, Code
  Usage, History, and Settings.
- WP-CLI commands for scan, diff, and CI checks.
- Git and CI integration that can fail on breaking changes.
- Source-health diagnostics that identify whether ACF field groups are aligned
  between the WordPress database and ACF Local JSON.
- Directional source-health guidance that explains whether a Local JSON change is
  eligible for ACF sync or a database-side group should be saved back to JSON.
- Code-impact analysis that connects ACF schema changes to concrete PHP field
  references, then presents actionable repair locations in Admin and CI output.
- A release-readiness workflow with declared compatibility, verification, and
  distribution documentation.
- A Free edition with core schema safety and a future Pro edition for advanced
  team workflows such as configurable risk policies and PR/MR reports.
- A live local workflow that compares the approved baseline with the current ACF
  runtime schema and scans the current PHP filesystem state without requiring a
  manual snapshot after every edit.
- An actionable Overview dashboard and a cohesive ACF-Pro-inspired plugin
  workspace that helps users understand baseline state, schema risk, source
  health, and the next safe action.

## 4. Data - What are we storing?

- Normalized ACF field-group schemas, including field keys, names, types,
  settings, nested fields, repeater subfields, and flexible-content layouts.
- Immutable schema snapshots with creation metadata and a source identifier,
  persisted in a dedicated WordPress database table rather than `wp_options`.
- Schema diffs and classified findings, including severity, rationale, and links
  to affected schema nodes.
- Code-usage references, including field name, scanner strategy, path, line, and
  source expression.
- Plugin settings, including scanner configuration and risk-rule policy.
- Source-health findings for field groups: aligned, database-only, JSON-only, or
  divergent between ACF Local JSON and the WordPress database.

## 5. Tech - What stack are we using?

- WordPress plugin written in PHP.
- A classic custom WordPress test theme written in PHP and plain CSS.
- ACF or ACF PRO when available, with ACF Local JSON as a primary test source.
- WP-CLI for local and CI execution.
- Git-compatible file layout and GitHub Actions or GitLab CI support later.
- Supported starting matrix: WordPress 6.4+, PHP 8.1+, and current supported
  ACF or ACF PRO releases. The plugin must work with ACF Free; ACF PRO is an
  optional enhancement, not a requirement.

## 6. Monetize - How will this make money?

Free provides core schema safety, Local JSON health, PHP code usage, WP-CLI, and
reports. Pro will add advanced team workflows without blocking access to stored
snapshots or Free safety features when a license is absent or expires.

## 7. UI/UX - How should this look and feel?

The production plugin should provide a compact, developer-first WordPress Admin
experience that makes the safety of recent ACF changes immediately clear. The
Overview must surface the current safety posture and the next practical action,
instead of being a static placeholder. The plugin workspace should be visually
cohesive and responsive, inspired by ACF Pro's clean tables, restrained white
surfaces, light-gray structure, and blue actions without imitating or restyling
ACF itself. The development theme is intentionally minimal, readable, and easy
to debug rather than production-oriented. Colour supports scanning, while text
labels and a legend preserve the meaning without relying on colour alone. The
plugin must not restyle the global WordPress Admin, ACF editor, or other
plugins.

## 8. Deployment - Where and how will this ship?

The plugin will be developed and tested in a local WordPress installation. CI
must later support GitHub Actions and GitLab CI through WP-CLI.

> TODO - decide distribution channel, release process, CI runtime, PHP matrix,
and public documentation site.
