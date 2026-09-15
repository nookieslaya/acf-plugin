# Feature: Complete translation source and Polish catalogue

**From build-plan:** feature 23b1
**Status:** planned

## Goal

Replace the initial screen-only POT with a complete source catalogue for the plugin Admin, then add a reviewed Polish `pl_PL` PO catalogue. English remains the source and fallback language until 23b2 compiles and verifies the runtime pack.

## In scope

- Regenerate `languages/acf-schema-guard.pot` from all extractable plugin Admin strings using WordPress-compatible tooling.
- Add `languages/acf-schema-guard-pl_PL.po` with Polish translations for the complete Admin workspace.
- Preserve literal English for field keys, file paths, schema keys, PHP examples, WP-CLI commands, command options, JSON property names, and code previews.
- Add assertions that the POT covers extractable source strings and that every POT message has a corresponding Polish PO entry.
- Validate both catalogue files with gettext tooling.

## Out of scope

- Compiling or loading a `.mo` file and live locale testing, deferred to 23b2.
- Translating CLI output, source code, ACF field values, user content, WordPress core, ACF, themes, or third-party plugins.
- Adding language-selection controls to the plugin. WordPress site or user locale remains the only language setting.

## Build loop

Build one step at a time, never the whole feature at once.

1. Plan mode lays out the step before any code.
2. The AI implements just that step.
3. It shows the diff (not full files); you read it and understand it.
4. You approve, then choose whether to commit a checkpoint or roll straight on.
   Checkpoints are optional; `/complete` makes the real feature-level commit at the end.

## Build steps

- [ ] **Step 1 - Generate complete translation source** - produce a full POT from extractable Admin strings and replace the initial screen-only template. *Done when:* all literal plugin UI messages that use `acf-schema-guard` are represented in the POT without adding technical identifiers as translatable source strings.
- [ ] **Step 2 - Add reviewed Polish catalogue** - create a `pl_PL` PO file and translate the complete Admin message set into natural Polish while preserving placeholders, markup, and technical literals exactly. *Done when:* the PO has an entry for every POT message and passes `msgfmt --check`.
- [ ] **Step 3 - Protect source and catalogue coverage** - add focused assertions for POT-to-source and PO-to-POT coverage, then run lint and the full Verify suite. *Done when:* an omitted UI string or a missing Polish message makes a test fail, and all automated checks pass.

## Files / areas

- `wp-content/plugins/acf-schema-guard/languages/acf-schema-guard.pot`
- `wp-content/plugins/acf-schema-guard/languages/acf-schema-guard-pl_PL.po`
- `wp-content/plugins/acf-schema-guard/tests/translation-contract-assertions.php`
- Plugin README translation instructions if the regeneration process changes.

## Data / contracts

- Locale: `pl_PL`.
- Text domain: `acf-schema-guard`.
- PO entries retain exact `msgid`, placeholders, HTML fragments, and translation context from POT; only `msgstr` is Polish.
- A blank or missing PO translation stays safe because WordPress falls back to the English `msgid`; this feature does not alter WordPress locale handling.

## Testing

- Run `msgfmt --check` against the Polish PO.
- Run the focused translation assertions and `sh wp-content/plugins/acf-schema-guard/tests/run-assertions.sh`.
- Inspect POT and PO with gettext tooling for malformed headers, duplicate message IDs, and placeholder mismatches.
- Live Polish and English Admin verification is deferred to 23b2, after the MO runtime pack exists.

## Notes for the AI

- Translate product language naturally for Polish WordPress users; retain ACF, Local JSON, baseline, field keys, severity values, and code where translating them makes troubleshooting harder.
- Do not claim a PO alone changes the visible WordPress interface. The compiled MO and real locale evidence belong to 23b2.
- Keep generated catalogue headers reproducible and do not add a runtime dependency for catalogue generation.
