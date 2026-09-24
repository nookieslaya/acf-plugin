# ACF Schema Guard user guide

## Core workflow

1. Capture a known-good schema in **History**, request a review, and set it as
   the approved baseline only after the review is approved.
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

## Safe Rename Assistant

When a direct ACF field name changes, **Changes** can show a Safe Rename
Assistant plan. It groups the old and new name, literal PHP call sites, and a
read-only dry-run of direct post-meta scope. The dry-run shows records using the
old key, records that already use the new key and therefore need manual conflict
review, and records that would require a later migration decision.

It never reads field values or changes data. Nested ACF fields are shown as not
safely simulatable, rather than receiving an unreliable migration estimate.

### Pro migration-plan foundation

When a local Pro preview or a future verified Pro entitlement is available, an
administrator can choose **Prepare Pro migration plan** for an eligible direct
rename. The saved plan contains only the old and new keys, current schema
identity, bounded record counts, conflict count, and review metadata. It does
not contain field values.

Before a future execution feature can use the plan, an administrator must add a
review note. If the live schema changes first, the plan is invalidated. This
release cannot execute a migration, copy a value, delete an old key, or roll
back content data.

## Team workflow

Use **History** as a small local review queue. The requester explains what a
snapshot should be reviewed for; a reviewer records an approval or rejection
with a note and timestamp. Snapshot records are immutable, and a new request
after a completed decision remains visible in the local audit history. This is
local WordPress workflow support, not a remote approval service.

Commit `acf-schema-baseline.json` for a portable baseline and optionally commit
`acf-schema-guard-policy.json` for a shared release threshold. The repository
policy takes priority over a local setting.

After approving a baseline, you can download the same portable JSON without a
terminal. In **History**, open the **Team baseline** card, verify the snapshot
ID and capture time, and choose **Download baseline JSON**. Save the file as
`acf-schema-baseline.json` in your versioned theme or plugin, then add and
commit it through your normal Git workflow. WordPress never writes repository
files or runs Git commands.

## Edition status

Version 1.0 keeps every safety-analysis workflow Free while the product is being
validated. The local development preview now also demonstrates the future Pro
migration-plan action; Free keeps its complete read-only Safe Rename Assistant.
A later commercial edition may configure the release threshold, create
auditable temporary exceptions, export release reports, and enable controlled
migration actions. Use **Changes** to download Markdown or JSON; use WP-CLI for
CI. A Markdown template may contain these markers:

```md
<!-- acf-schema-guard:summary -->
<!-- acf-schema-guard:findings -->
```
