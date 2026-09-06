# Feature: Plugin Admin change visual system

**From build-plan:** feature 11d
**Status:** verified

## Goal

Make schema findings in the ACF Schema Guard Changes screen fast to scan on
desktop and usable on narrow screens. Severity must be communicated by readable
text and a legend, with colour used only as an additional visual signal.

## Design reference

The user-provided screenshot from 2026-09-06 documents the current Changes
screen: the six-column table overflows its content area, long paths dominate the
layout, details become a narrow vertical column, and severity has only minimal
text colouring. It is a current-state reference, not a pixel-matching target.

## In scope

- The ACF Schema Guard Changes screen and its empty, success, and error states.
- A compact legend for `safe`, `warning`, `high`, and `critical` severities.
- Accessible severity badges with persistent text labels and supporting colours.
- Readable finding grouping, spacing, path wrapping, and explanation details.
- A responsive presentation that remains understandable at narrow WordPress
  Admin viewport widths without horizontal page overflow.
- Styles scoped beneath ACF Schema Guard classes so they cannot leak into other
  WordPress Admin or ACF screens.

## Out of scope

- Restyling the global WordPress Admin, ACF editor, test theme, or plugin screens
  that do not display schema changes.
- Changing risk classification, explanation wording, snapshot data, CLI output,
  or comparison behavior.
- JavaScript interactions, filtering, sorting, pagination, charts, or a new CSS
  framework/build pipeline.
- A full visual redesign of every ACF Schema Guard administration screen.

## Build loop

Build one step at a time, never the whole feature at once.

1. Plan mode lays out the step before any code.
2. The AI implements just that step.
3. It shows the diff (not full files); you read it and understand it.
4. You approve, then choose whether to commit a checkpoint or roll straight on.
   Checkpoints are optional; `/complete` makes the real feature-level commit at the end.

Never accept a step you haven't read. If a diff is too big to review, the step was too big, so split it.

## Build steps

- [x] **Step 1 - Add semantic visual hooks and severity legend** - update the
  Changes result markup with a labelled severity legend, normalized badge labels,
  finding severity classes, and per-cell labels needed by the narrow-screen
  presentation while preserving table semantics and escaped output. *Done when:*
  all four risk levels are explained in visible text, every finding exposes its
  severity as text, and existing comparison data and behavior are unchanged.
- [x] **Step 2 - Build the desktop change visual system** - extend the existing
  plugin admin stylesheet with scoped colour tokens, badges, restrained row
  accents, readable column sizing, paths, summaries, and detail lists. *Done
  when:* the Changes screen clearly distinguishes each severity without relying
  only on colour, long values wrap instead of crushing the details column, and
  no styles apply outside ACF Schema Guard pages.
- [x] **Step 3 - Add the responsive findings layout** - use a focused media query
  to transform each finding into a readable stacked record on narrow screens,
  retaining labels and logical reading order without JavaScript. *Done when:*
  the screen has no page-level horizontal overflow at phone-sized widths and all
  kind, node, path, explanation, severity, and rationale values remain visible.
- [x] **Step 4 - Verify markup safety and real Admin rendering** - extend the
  isolated Admin rendering assertions for the legend, labels, severity-class
  allow-list, and escaped content; lint changed PHP and inspect the live Changes
  screen at desktop and narrow widths. *Done when:* assertions and PHP lint pass,
  the supplied comparison remains readable in both viewport classes, and the
  browser shows no relevant console errors.

## Files / areas

- `wp-content/plugins/acf-schema-guard/includes/admin/class-admin-controller.php`
- `wp-content/plugins/acf-schema-guard/assets/css/admin.css`
- `wp-content/plugins/acf-schema-guard/tests/admin-change-explanation-assertions.php`

## Data / contracts

- No database, snapshot, normalized-schema, analysis, or CLI contract changes.
- Existing severity values remain `safe`, `warning`, `high`, and `critical`.
- CSS classes derived from severity must use an explicit allow-list before being
  rendered, even though classifier output is currently trusted.
- Responsive cell labels are presentation metadata only and do not alter the
  finding payload.

## Testing

- Run the focused isolated Admin rendering assertion script after markup changes.
- Run all existing plugin assertion scripts to catch rendering and analysis
  regressions; no formal test runner or Browser tests command is configured.
- Run `php -l` on every changed PHP file and `git diff --check`.
- In the authenticated local WordPress Admin, open **ACF Schema Guard > Changes**
  using the existing baseline/current pair. Check desktop and phone-sized widths,
  confirm all severity states represented by fixtures have a text label, and
  inspect the browser console for relevant errors.
- Use screenshot evidence for desktop and narrow layouts because visual fidelity
  and responsive behavior are not proven by isolated PHP assertions.

## Notes for the AI

- Use plain CSS and existing WordPress Admin conventions; add no dependency or
  build step.
- Keep selectors under `.acf-schema-guard-admin` and change-specific classes.
- Use high-contrast foreground, background, and border combinations. Colour must
  never replace the severity word or legend description.
- Preserve native table semantics for assistive technology even when CSS presents
  rows as stacked records visually.
- Keep source order meaningful and avoid duplicate hidden content solely for
  desktop/mobile variants.
- Do not touch the user's modified ACF Local JSON fixtures.

## Completion evidence

- All plugin assertion scripts passed on 2026-09-06.
- PHP lint passed for the changed Admin controller and assertion script.
- `git diff --check` passed.
- The user confirmed the desktop and responsive Changes screen in the local
  WordPress Admin.
