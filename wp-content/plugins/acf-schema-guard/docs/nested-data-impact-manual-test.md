# Nested data-impact manual test

This isolated development fixture verifies each supported stored-data evidence
pattern in one baseline-to-current comparison. It never asks the plugin to read
or display the values you enter.

## 1. Prepare the fixture

1. Go to **ACF → Field Groups** and open **Schema Guard - Nested Impact Fixture**.
2. Change its status to **Active**, then save it. This writes the fixture to the
   local development database and keeps its Local JSON definition in the theme.
3. Create a new Page titled `ACF Schema Guard Nested Impact Fixture` and enter a
   non-empty value in every fixture field:
   - Direct value;
   - Group value → Group child;
   - Repeater value → add one row → Repeater child;
   - Flexible value → add Fixture content → Flexible child;
   - Flexible value → Fixture content → Flexible repeater → add one row →
     Flexible repeater child.
4. Publish or update that Page.

## 2. Set the baseline

1. Go to **ACF Schema Guard → History** and use **Capture a checkpoint**.
2. Go to **ACF Schema Guard → Overview** and set that checkpoint as the approved
   baseline if you have not already set one.

## 3. Create the five schema changes

In **ACF → Field Groups → Schema Guard - Nested Impact Fixture**, rename only
the following field names. Do not change their keys.

| Current field name | Rename to | Expected storage pattern |
| --- | --- | --- |
| `acf_schema_guard_impact_fixture_direct` | `acf_schema_guard_impact_fixture_direct_v2` | `acf_schema_guard_impact_fixture_direct` |
| `acf_schema_guard_impact_fixture_group_child` | `acf_schema_guard_impact_fixture_group_child_v2` | `acf_schema_guard_impact_fixture_group_acf_schema_guard_impact_fixture_group_child` |
| `acf_schema_guard_impact_fixture_repeater_child` | `acf_schema_guard_impact_fixture_repeater_child_v2` | `acf_schema_guard_impact_fixture_repeater_{row}_acf_schema_guard_impact_fixture_repeater_child` |
| `acf_schema_guard_impact_fixture_flexible_child` | `acf_schema_guard_impact_fixture_flexible_child_v2` | `acf_schema_guard_impact_fixture_flexible_{row}_acf_schema_guard_impact_fixture_flexible_child` |
| `acf_schema_guard_impact_fixture_flexible_repeater_child` | `acf_schema_guard_impact_fixture_flexible_repeater_child_v2` | `acf_schema_guard_impact_fixture_flexible_{row}_acf_schema_guard_impact_fixture_flexible_repeater_{row}_acf_schema_guard_impact_fixture_flexible_repeater_child` |

Save the field group after all five renames. The plugin’s automatic snapshot hook
captures the new schema only when its normalized hash changed. If automatic
capture is unavailable, create a new checkpoint manually in History.

## 4. Review the result

Open **ACF Schema Guard → Changes**. Compare the approved baseline with the live
current schema, or select the two captured checkpoints.

Expected result:

- Every renamed field is a **High** schema change.
- The direct field shows **Direct storage** and one matching record.
- The remaining four fields show **Nested ACF storage**, their field path, and
  the table pattern above with `{row}` in the repeatable positions.
- The same fixture Page is listed as a record to review for each evidence row.
- No entered value is shown anywhere in ACF Schema Guard.

## Coverage unknown

**Coverage unknown** is covered by the automated Admin rendering test. It is not
manufactured in this manual fixture because ACF Clone storage can depend on clone
configuration and does not provide a portable child-meta pattern that the plugin
should pretend to understand.

## Clean up

After testing, either leave the inactive fixture group for future development
tests or delete it through ACF. Removing the group is itself a valid schema-risk
scenario, so do not use it as a baseline unless you intend to review that change.
