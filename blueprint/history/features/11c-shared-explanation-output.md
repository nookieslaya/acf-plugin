# Feature: Shared explanation output

**From build-plan:** feature 11c
**Status:** verified

## Goal

Expose the same deterministic schema-change explanation in every snapshot
analysis result, then render it consistently in the ACF Schema Guard Changes
screen and the snapshot-based WP-CLI commands. This makes the explanations built
in 11a and 11b visible to users for the first time.

## In scope

- Enrich each classified finding with one shared `explanation` object generated
  by `SchemaChangeExplainer`.
- Preserve all existing analysis, severity, rationale, and process exit behavior.
- Include explanations in JSON and table output for `diff`, `check`, and
  `baseline check`.
- Replace the Admin screen's limited local change formatter with the shared
  explanation summary and detail lines.
- Escape every Admin explanation at render time and handle empty details safely.
- Document a concrete manual test using the approved baseline and a new snapshot.

## Out of scope

- Severity colours, badges, responsive table styling, legends, and visual
  grouping, deferred to feature 11d.
- Translating the explainer's domain text or changing its established wording.
- New diff rules, risk rules, snapshot behavior, CLI commands, or database data.
- Altering ACF field groups or the development theme as part of implementation.

## Build loop

Build one step at a time, never the whole feature at once.

1. Plan mode lays out the step before any code.
2. The AI implements just that step.
3. It shows the diff (not full files); you read it and understand it.
4. You approve, then choose whether to commit a checkpoint or roll straight on.
   Checkpoints are optional; `/complete` makes the real feature-level commit at the end.

Never accept a step you haven't read. If a diff is too big to review, the step was too big, so split it.

## Build steps

- [x] **Step 1 - Add the shared analysis contract** - make snapshot analysis
  attach `SchemaChangeExplainer` output to every classified finding and update
  composition and focused assertions. Keep the addition backward-compatible for
  existing consumers of `change`, `severity`, and `rationale`. *Done when:* each
  finding contains `explanation.summary` and `explanation.details`, no-change
  analyses remain empty, and focused snapshot-analysis assertions pass.
- [x] **Step 2 - Use explanations in WP-CLI** - show the shared summary and
  semicolon-separated details in table output for `diff`, `check`, and
  `baseline check`; JSON output must expose the unchanged structured explanation
  object. Extend the existing CLI assertions, including empty details and
  `--fail-on-breaking`. *Done when:* all three command paths use the shared
  explanation without changing their validation, no-change messages, or failure
  status behavior.
- [x] **Step 3 - Use explanations in WordPress Admin** - remove the Changes
  screen's local three-property formatter and render the shared summary plus each
  detail as escaped HTML. Keep the current empty and comparison-error states.
  Add proportionate rendering assertions or a focused test seam, then run the
  full PHP assertion suite and syntax checks. *Done when:* a real baseline versus
  current comparison displays exact before-to-after details in Admin, all output
  is escaped, all existing assertion scripts pass, and `git diff --check` passes.

## Files / areas

- `wp-content/plugins/acf-schema-guard/includes/diff/class-snapshot-analysis.php`
- `wp-content/plugins/acf-schema-guard/includes/diff/class-snapshot-analysis-service.php`
- `wp-content/plugins/acf-schema-guard/includes/class-plugin.php`
- `wp-content/plugins/acf-schema-guard/includes/cli/class-finding-output-formatter.php`
- `wp-content/plugins/acf-schema-guard/includes/cli/class-diff-command.php`
- `wp-content/plugins/acf-schema-guard/includes/cli/class-check-command.php`
- `wp-content/plugins/acf-schema-guard/includes/cli/class-baseline-command.php`
- `wp-content/plugins/acf-schema-guard/includes/admin/class-admin-controller.php`
- Existing focused PHP assertion scripts under `wp-content/plugins/acf-schema-guard/tests/`

## Data / contracts

- Extend each `SnapshotAnalysis::to_array()['findings'][]` value additively with:
  `explanation => array{summary:string,details:string[]}`.
- Preserve existing finding keys and meanings: `change`, `severity`, and
  `rationale`.
- Table details are a presentation-only join of `explanation.details` using
  `; `; JSON retains the array.
- The Admin view renders the summary separately from zero or more detail lines.
- The shared analysis contract is load-bearing for Admin, WP-CLI, and future
  output consumers.

## Testing

- There is no configured test runner, browser harness, or Verify command in
  `AGENTS.md`.
- Extended snapshot-analysis assertions for the additive explanation contract
  and unchanged empty analysis.
- Extended WP-CLI diff, check, and baseline assertions for table and JSON output,
  no-change handling, and preserved breaking-change failure behavior.
- Added a focused assertion for escaped Admin explanation rendering.
- Ran `php -l` on every changed PHP file, all existing
  `tests/*-assertion*.php` scripts, and `git diff --check`.
- Verified the real Local WP-CLI output and Admin controller rendering against
  the approved baseline and newest snapshot; the user supplied a matching Admin
  screenshot.

## Notes for the AI

- Generate explanations once in the analysis layer; Admin and CLI must not
  reimplement property comparison logic.
- Treat analysis arrays defensively at output boundaries and never render raw
  `before` or `after` structures as HTML.
- Keep CLI JSON machine-readable and additive so existing CI consumers remain
  compatible.
- Do not add styling that belongs to 11d.
- Do not touch or stage the user's local ACF JSON test changes.
