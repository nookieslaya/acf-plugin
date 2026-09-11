# Feature: Edition policy and feature catalogue

**From build-plan:** feature 16a

**Status:** verified and locally merged

## Goal

Define a clear, user-safe Free and Pro product policy before licensing code is
introduced.

## Delivered

- A one-plugin product model where Free safety workflows remain available
  without a key and Pro adds team-oriented workflows.
- Annual Pro plan definitions for 1 site, 5 sites, and unlimited sites.
- A vendor-neutral license lifecycle with periodic validation, a 30-day offline
  grace period, and source-of-truth rules for a future licensing service.
- A feature catalogue that preserves existing schema, source health, code impact,
  WP-CLI, and reporting capabilities as Free.
- Explicit future Pro boundaries for configurable risk policies, pull-request
  and merge-request workflows, and shared team governance.
- A README link to the policy documentation.

## Data and safety contract

- A future license authority owns the plan, status, expiry, activation, and
  allowed-site count.
- WordPress may cache only the minimal last-verified license state.
- A license result never deletes, alters, hides, encrypts, or makes unavailable
  snapshots, reports, scanner settings, ACF data, or WordPress content.
- Expiry disables only future Pro actions after the grace period; Free workflows
  and previously generated evidence remain readable and exportable.

## Verification

- `sh wp-content/plugins/acf-schema-guard/tests/run-assertions.sh` passed.
- PHP lint passed for the user-requested changed theme templates.
- JSON parsing passed for the user-requested changed and `__trashed` ACF Local
  JSON fixtures.
- `git diff --check` passed for the feature-owned paths and requested theme
  fixtures.

## Follow-up

Feature 16b will add a testable, provider-neutral capability boundary. It will
not add checkout, a remote activation service, payment handling, or a customer
portal.
