# Feature: Automatic deduplicated schema history

**From build-plan:** feature 17c
**Status:** verified

## Goal

Create a plugin snapshot after a supported ACF field-group save only when the
normalized schema changed. Keep manual capture as an explicit checkpoint.

## In scope

- A deterministic schema hash for deduplication against the latest automatic
  snapshot.
- ACF save-hook integration that runs after a field-group save and fails safely
  when ACF is unavailable.
- Clear automatic snapshot source metadata and focused assertions.

## Out of scope

- Capturing on every WordPress request, file watcher, cron, or background job.
- Changing the approved baseline automatically.
- Capturing ACF content values, post meta, or user data.

## Build steps

- [x] **Step 1 - deduplication service** - compare the current normalized schema
  with the latest automatic snapshot by canonical hash. *Done when:* equal
  schemas do not insert; changed schemas insert exactly once.
- [x] **Step 2 - ACF save integration** - register the supported post-save hook
  and call the service only for ACF field groups. *Done when:* manual capture
  remains unchanged and unavailable ACF is a no-op.
- [x] **Step 3 - history clarity** - label automatic entries in History without
  altering baseline selection. *Done when:* users can distinguish automatic and
  manual captures.
- [x] **Step 4 - regression and manual evidence** - run the full suite and save
  an unchanged then changed ACF group manually. *Done when:* only the changed
  save adds an automatic snapshot.

## Testing

- Add isolated tests for equal and changed canonical schemas, hook filtering,
  and repository inserts.
- Run `sh wp-content/plugins/acf-schema-guard/tests/run-assertions.sh`.

## Notes for the AI

- Keep writes limited to the plugin snapshots table.
- Do not touch the user's uncommitted test-theme files.
