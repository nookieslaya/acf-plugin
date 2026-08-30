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

## 4. Data - What are we storing?

- Normalized ACF field-group schemas, including field keys, names, types,
  settings, nested fields, repeater subfields, and flexible-content layouts.
- Immutable schema snapshots with creation metadata and a source identifier.
- Schema diffs and classified findings, including severity, rationale, and links
  to affected schema nodes.
- Code-usage references, including field name, scanner strategy, path, line, and
  source expression.
- Plugin settings, including scanner configuration and risk-rule policy.

## 5. Tech - What stack are we using?

- WordPress plugin written in PHP.
- A classic custom WordPress test theme written in PHP and plain CSS.
- ACF or ACF PRO when available, with ACF Local JSON as a primary test source.
- WP-CLI for local and CI execution.
- Git-compatible file layout and GitHub Actions or GitLab CI support later.
- Minimum supported PHP version: > TODO - decide before plugin implementation.

## 6. Monetize - How will this make money?

> TODO - monetization and licensing are not decided for v1 planning.

## 7. UI/UX - How should this look and feel?

The production plugin should provide a compact, developer-first WordPress Admin
experience that makes the safety of recent ACF changes immediately clear. The
development theme is intentionally minimal, readable, and easy to debug rather
than production-oriented.

## 8. Deployment - Where and how will this ship?

The plugin will be developed and tested in a local WordPress installation. CI
must later support GitHub Actions and GitLab CI through WP-CLI.

> TODO - decide distribution channel, release process, CI runtime, PHP matrix,
and public documentation site.
