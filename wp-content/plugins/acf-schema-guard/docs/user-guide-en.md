# ACF Schema Guard user guide

## Core workflow

1. Capture a known-good schema in **History** and set it as the approved
   baseline.
2. Change and save ACF fields.
3. Open **Changes**. It compares the baseline with the live schema.
4. Review severity, code references, and stored-data impact before deployment.

## Reading findings

`Safe` needs no expected action. `Warning` needs review. `High` is likely to
break existing code or data usage. `Critical` should be resolved before release.
Changes keeps the original risk visible even when a Pro exception is active.

## Source and code health

**Field Groups** compares database definitions with ACF Local JSON. In Solo
Mode, Local JSON is optional and the plugin uses a database-first workflow.
**Code Usage** lists literal supported PHP ACF calls. **Unused Fields** is only
a review signal: dynamic calls or unscanned folders mean a field must not be
assumed safe to delete.

## Data impact

For renamed or removed fields, Changes can show bounded matching record IDs.
It never reads field values. Direct and supported nested storage are evidence,
not a complete migration plan.

## Team workflow

Commit `acf-schema-baseline.json` for a portable baseline and optionally commit
`acf-schema-guard-policy.json` for a shared release threshold. The repository
policy takes priority over a local setting.

## Pro workflow

Pro can configure the release threshold, create auditable temporary exceptions,
and export release reports. Exceptions preserve the original finding and expire
or can be revoked. Use **Changes** to download Markdown or JSON; use WP-CLI for
CI. A Markdown template may contain these markers:

```md
<!-- acf-schema-guard:summary -->
<!-- acf-schema-guard:findings -->
```
