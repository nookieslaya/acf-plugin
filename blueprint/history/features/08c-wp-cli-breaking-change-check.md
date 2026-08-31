# Feature: WP-CLI breaking-change check

**From build-plan:** feature 8c
**Status:** verified

## Goal

Add a CI-oriented snapshot check which fails only for existing `high` and
`critical` risk findings.

## Contract

`wp acf-schema-guard check <before-id> <after-id> [--format=table|json] [--fail-on-breaking]`

Without the flag, valid analysis exits successfully. With the flag, high or
critical findings produce a non-zero WP-CLI error after the analysis output.
The command is read-only.

## Testing

The isolated CLI check test covers a safe no-change pass and a real field-type
change that fails. All eleven plugin assertion scripts passed.
