# Feature: Pro risk policies

**From build-plan:** feature 16c1
**Status:** verified

## Goal

Give Pro users an explicit release-risk threshold while keeping the established
Free threshold and all existing safety checks unchanged.

## In scope

- A named policy with a `fail_on` severity of warning, high, or critical.
- Capability-gated configuration and preview in Admin.
- A downloadable, repository-owned team policy file that every edition can read.
- Policy-aware CLI baseline check and report data, without changing default Free
  output.
- Readable explanation of the effective policy.

## Out of scope

- Exceptions, PR/MR publishing, remote policy sharing, license activation, or
  automatic schema migration.

## Build steps

- [x] **Step 1 - Policy contract and evaluator** - add a validated policy value
  object and deterministic severity evaluator. *Done when:* each severity maps to
  an explicit pass or fail result under the selected threshold.
- [x] **Step 2 - Capability-gated persistence** - allow only an available Pro
  capability to save a policy, otherwise retain the Free default. *Done when:* a
  Free site cannot change or corrupt the effective policy.
- [x] **Step 3 - Admin policy screen** - present the effective policy, locked
  Free state, and local Pro-preview configuration. *Done when:* a Pro preview can
  choose and save a threshold with nonce and capability protection.
- [x] **Step 4 - CLI and assertions** - use the effective policy in release
  checks and cover Free, Pro, malformed input, and all severity boundaries.
  *Done when:* full Verify passes and existing CLI defaults remain unchanged.
- [x] **Step 5 - Versioned team policy** - allow Pro to download policy JSON and
  let Free and Pro read a configured repository file as the effective shared
  policy. *Done when:* a normal Git pull makes the policy visible to all editions
  without plugin CLI setup.

## Data / contracts

- `fail_on`: `warning`, `high`, or `critical`.
- Free default is `high`, matching current `--fail-on-breaking` behaviour.
- The policy option stores only the selected threshold, never a license state.
- A team policy JSON file is read-only to the plugin and overrides the local
  option when present. Browser download never writes into the repository.

## Testing

- Focused PHP assertions plus repository Verify.
- Manual Admin test with and without the local Pro preview constant.
