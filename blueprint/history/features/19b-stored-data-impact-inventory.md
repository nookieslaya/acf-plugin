# Feature: Stored data impact inventory

**From build-plan:** feature 19b

**Status:** verified and locally merged

## Goal

Show safe, bounded evidence of WordPress records that directly store meta for a
field removed or renamed by the selected schema comparison.

## Delivered

- Read-only analysis for removed fields, renamed fields, and fields contained in
  removed field groups.
- Exact post-meta-key matching with a distinct record count and a sample capped
  at 20 records.
- Changes-panel evidence showing the affected field name, count, and safe post
  identifiers, including an explicit zero-record state.
- Focused assertions for query boundaries, deduplication, and Admin rendering.

## Data contract and limits

- The analyzer matches only `postmeta.meta_key = previous_field_name`.
- It never reads, renders, exports, writes, or changes `meta_value`.
- Returned records contain only post ID, type, status, and title.
- Nested ACF storage, repeaters, options, terms, users, comments, and wildcard
  matching are deliberately outside this first version. A zero count does not
  prove that no stored value exists elsewhere.

## Verification

- PHP lint passed for all changed PHP files.
- `sh wp-content/plugins/acf-schema-guard/tests/run-assertions.sh` passed.
- Scoped whitespace check passed for the plugin and feature-spec changes.
- Manual WordPress Admin verification confirmed a renamed field reported the
  expected two literal PHP call sites and `0 direct records found` for the old
  meta key.
