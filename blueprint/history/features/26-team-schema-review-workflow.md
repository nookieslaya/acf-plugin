# Feature: Team schema review workflow

**From build-plan:** feature 26a and 26b
**Status:** verified

## Goal

Let WordPress administrators request review of a stored schema snapshot, record
an auditable approval or rejection with a required note, and use only an
approved request to set that snapshot as the approved baseline.

## In scope

- Store bounded local review records in a dedicated WordPress option.
- Record request author, request note and timestamp, plus reviewer, decision
  note and timestamp.
- Let administrators request review, approve, reject, and set an approved
  snapshot as the baseline with nonce and capability protection.
- Show pending and completed review state in History and a concise pending count
  on Overview.
- Keep the workflow available in the Free test release without a license key.

## Out of scope

- Email, Slack, GitHub, GitLab, remote accounts, webhooks, or background jobs.
- New WordPress roles, mandatory four-eyes approval, or enforcement that a
  requester and reviewer are different users.
- Editing snapshots, ACF definitions, post meta, or content.
- Production licensing and feature gating, deferred to feature 25.

## Build steps

- [x] **Step 1 - Review record and service** - add a validated record model and
  bounded persistence service for snapshot review requests and decisions.
  *Done when:* invalid data is rejected, only pending requests can be decided,
  and an approved record can be resolved by snapshot ID.
- [x] **Step 2 - Protected review actions** - connect request, approval,
  rejection and approved-baseline actions to the existing Admin controller.
  *Done when:* every state-changing action requires `manage_options` and a
  nonce; a non-approved snapshot cannot become the baseline through this flow.
- [x] **Step 3 - Review queue and history UI** - add History controls and an
  Overview summary using the existing Admin visual system.
  *Done when:* users can see who requested or decided a review, its note and
  timestamp, and have an understandable empty state.
- [x] **Step 4 - Verification and documentation** - add focused assertions,
  update English and Polish guidance, compile the Polish runtime catalogue, and
  run the repository Verify command.
  *Done when:* tests cover the service and Admin contract; PHP lint, gettext
  validation and `tests/run-assertions.sh` pass.

## Files / areas

- `includes/review/` - review record and persistence service.
- `includes/admin/class-admin-controller.php` - protected actions and screens.
- `includes/class-plugin.php` - shared review service composition.
- `assets/css/admin.css` - only the necessary workspace styles.
- `tests/` - focused review workflow assertions.
- `languages/`, `readme.md`, and user guides - translated user-facing guidance.

## Data / contracts

- `snapshot_id` is a stored immutable snapshot UUID.
- Review status is `pending`, `approved`, or `rejected`.
- Request note and decision note are required non-empty bounded plain text.
- Each snapshot has at most one current review record. Re-requesting after a
  completed decision creates a new pending record; completed records remain in
  the bounded local audit history.
- Approval sets the existing approved-baseline option only through an explicit
  follow-up action. Rejection never changes the baseline.

## Testing

- Added deterministic service assertions for validation, transitions, and
  approved-state lookup.
- Added Admin-contract assertions for the review actions and baseline guard.
- Ran `msgfmt --check`, PHP lint, and
  `sh wp-content/plugins/acf-schema-guard/tests/run-assertions.sh`.
- Manual review passed: the user tested feature 26 in WordPress Admin.

## Notes for the AI

- Use the existing ACF Schema Guard Admin cards, tables and responsive patterns.
- Do not call this a license or a Pro-only feature in the current public UI.
- Keep review notes out of CLI output and external reports unless a later
  explicitly planned feature adds that contract.
