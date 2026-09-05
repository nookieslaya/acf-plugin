# Feature: Snapshot history and capture

**From build-plan:** feature 10a
**Status:** verified

## Goal

Make the Admin History screen useful: administrators can review stored immutable
snapshots and capture the current ACF schema without using WP-CLI. The action
must be protected and fail safely when ACF is unavailable.

## In scope

- Retrieve stored snapshots in deterministic newest-first order.
- Render a History table with snapshot ID, source ID, and UTC creation time.
- Render a clear empty state when no snapshots exist.
- Provide a capability- and nonce-protected "Capture current schema" action.
- Redirect back to History with a generic success or failure notice.

## Out of scope

- Selecting or comparing snapshots, which belongs to feature 10b.
- Deleting, editing, exporting, or scheduling snapshots.
- New settings, REST endpoints, AJAX, or changes to ACF field definitions.

## Build loop

Build one step at a time, never the whole feature at once.

1. The AI implements only the current checked-off step.
2. It shows the diff and the evidence for that step.
3. You review and approve before the next step or an optional checkpoint commit.
4. `/complete` makes the feature-level commit only after all steps and final
   verification pass.

## Build steps

- [x] **Step 1 - Add a snapshot-history repository contract.** Add an explicit
  repository method that returns immutable `SchemaSnapshot` objects ordered by
  `created_at DESC, id DESC`, and cover its SQL and reconstruction contract in
  the existing assertion scripts. *Done when:* the focused assertion proves the
  returned order and snapshot reconstruction, and PHP syntax checks pass.
- [x] **Step 2 - Render stored snapshots in Admin History.** Compose the
  repository into the Admin controller and replace the History placeholder with
  a read-only table and an empty state, while leaving the other foundation
  screens unchanged. *Done when:* an administrator can open History and see the
  stored snapshot ID, source ID, and creation time, or a specific no-snapshots
  message; changed PHP files pass syntax checks.
- [x] **Step 3 - Add protected manual snapshot capture.** Add an `admin-post`
  handler with `manage_options` and nonce checks, use the existing capture
  service through the plugin composition root, and redirect to History with a
  generic success or failure notice. *Done when:* submitting the History form
  creates one new `admin-manual` snapshot when ACF is available, returns to
  History with a success notice, and invalid authorization never captures data.

## Files / areas

- `includes/snapshots/interface-snapshot-repository.php` - list contract.
- `includes/snapshots/class-wordpress-snapshot-repository.php` - deterministic
  WordPress query and reconstruction.
- `includes/class-plugin.php` - compose the Admin controller with the existing
  snapshot and capture boundaries.
- `includes/admin/class-admin-controller.php` - History rendering, protected
  form handling, notices, and redirects.
- `assets/css/admin.css` - limited table and action spacing where needed.
- `tests/snapshot-persistence-assertions.php` - focused repository contract
  coverage.

## Data / contracts

- `SnapshotRepository::all()` returns `SchemaSnapshot[]`, newest first by
  `created_at DESC, id DESC`; it returns an empty array when storage is empty.
  This is load-bearing for feature 10b.
- Admin capture uses the fixed source ID `admin-manual`.
- The action name is `acf_schema_guard_capture_snapshot`; its nonce action is
  `acf_schema_guard_capture_snapshot`.
- The capture endpoint requires `manage_options`, never trusts request data for
  the source ID, and redirects only to the plugin History page.

## Testing

- No configured unit-test runner or browser harness exists. Extend the existing
  PHP assertion script for repository logic and run all plugin assertion scripts.
- Run `php -l` for every changed PHP file and `git diff --check`.
- In Local, open **ACF Schema Guard -> History**, submit **Capture current
  schema**, confirm one new `admin-manual` row and success notice, then reload
  to confirm the row remains. Verify the empty state in an isolated empty table
  or through the focused rendering path.
- Confirm the action remains unavailable to a user lacking `manage_options`.

## Notes for the AI

- Follow WordPress Coding Standards and escape at rendering time.
- Do not expose raw database or ACF exception text in Admin notices.
- Keep the snapshot table append-only. This feature only reads existing records
  and adds new immutable captures.
- Do not add dependencies, JavaScript, a build step, REST, AJAX, or settings.
