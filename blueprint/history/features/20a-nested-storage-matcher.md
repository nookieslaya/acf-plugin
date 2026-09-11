# Feature: Nested storage matcher

**From build-plan:** feature 20a
**Status:** verified

## Goal

Extend stored-data impact analysis so a renamed or removed nested ACF field can
produce bounded, read-only evidence for storage keys that are provable from its
schema ancestry. This establishes a safe contract for the later Changes
presentation without reading values, changing content, or presenting guesses as
evidence.

## Delivered

- Added immutable schema-derived matchers for exact direct keys and nested Group,
  Repeater, and Flexible Content key structures.
- Preserved ancestry in field-level schema changes, so a nested rename can be
  analyzed from its pre-change parent structure.
- Used numeric-row regular expressions for repeater and flexible rows, instead
  of broad wildcard matching.
- Extended bounded post-meta evidence queries without selecting or exposing
  `meta_value`.
- Retained `unknown` evidence for unsupported structures such as Clone fields,
  without querying or guessing a storage key.

## Data contract and limits

- Each impact now includes a matcher with `field_name`, `field_path`, display
  `pattern`, query type, and confidence (`direct`, `nested`, or `unknown`).
- Only normalized pre-change structure is used to locate legacy stored data.
- Queryable matcher samples remain limited to 20 post identifiers with type,
  status, and title. No values are read, stored, rendered, exported, or changed.
- Options, users, terms, comments, blocks, arbitrary prefixes, Clone fields, and
  migrations remain outside this feature.

## Verification

- PHP lint passed for changed PHP files.
- `sh wp-content/plugins/acf-schema-guard/tests/run-assertions.sh` passed.
- Focused assertions cover direct keys, Group, Repeater, Flexible Content,
  removed groups, malformed/unsupported structures, bounded prepared queries,
  and a real schema-diff-to-impact flow.

## Follow-up

Feature 20b will present direct, nested, and unknown evidence in Changes with
clear explanations of the supported storage coverage and its limits.
