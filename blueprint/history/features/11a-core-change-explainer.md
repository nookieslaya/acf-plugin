# Feature: Core change explainer

**From build-plan:** feature 11a
**Status:** verified

## Completed work

- Added a presentation-neutral `SchemaChangeExplainer` with stable `summary`
  and ordered `details` output.
- Explained core modified field-group properties (title and active state) and
  field properties (name, label, type, required state, instructions, and
  default value).
- Added safe descriptions for added and removed fields and field groups.
- Added deterministic formatting for empty values, null, booleans, numbers, and
  structured default values.

## Verification

- All repository PHP assertion scripts passed, including focused explainer
  assertions.
- PHP lint passed for the new explainer and the plugin bootstrap.
- `git diff --check` passed.

## Follow-up

- Feature 11b will cover type-specific settings and nested structures.
- Feature 11c will render these explanations in Admin and WP-CLI.
