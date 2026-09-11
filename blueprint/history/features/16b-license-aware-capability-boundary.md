# Feature: License-aware capability boundary

**From build-plan:** feature 16b
**Status:** verified

## Goal

Provide a provider-neutral answer to whether a named Pro capability is available,
with a local-only Free and Pro preview path that does not contact a licensing
service or restrict any existing Free workflow.

## Delivered

- Named Pro capability identifiers for configurable risk policies and
  review-ready reports.
- Immutable license-state and capability-decision contracts.
- A pure capability service with safe denial reasons.
- A plugin filter seam for a later verified licensing provider.
- A local-only development token accepted only in WordPress `local`.
- A reusable accessible Admin Pro-feature notice for later workflows.
- Focused assertions and local-preview documentation.

## Verification

- `sh wp-content/plugins/acf-schema-guard/tests/run-assertions.sh`
- `php -l` for the composition root, licensing classes, and notice class.
- WP-CLI runtime check: the default state denied a Pro capability with the
  expected Free-safe reason.

## Constraints retained

- No production key entry, remote activation, payment, checkout, telemetry,
  provider integration, or customer-data storage.
- Existing Free workflow and stored plugin data remain available.
