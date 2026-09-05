# ACF Schema Guard - Build Plan

## Foundation

- [x] 1. **ACF development test theme** - provide a minimal classic theme with ACF Local JSON, realistic field examples, and documented breaking-change scenarios.
- [x] 2. **Plugin bootstrap and ACF integration boundary** - create the plugin shell and safely discover ACF, ACF PRO, field groups, and Local JSON sources.
- [x] 3. **Schema normalization and snapshots**
  - [x] 3a. **Normalized schema model** - transform ACF discovery data into stable, deterministic schema contracts without persistence.
  - [x] 3b. **Snapshot persistence** - persist and retrieve immutable normalized snapshots in a dedicated WordPress table.
  - [x] 3c. **Snapshot capture service** - compose ACF discovery, normalization, and persistence into explicit snapshot capture.

## Change analysis

- [x] 4. **Schema diff and extensible risk classification**
  - [x] 4a. **Schema change model** - compare normalized snapshots and report deterministic added, removed, and modified groups and fields.
  - [x] 4b. **Risk classification rules** - classify schema changes as safe, warning, high, or critical through extensible rules.
  - [x] 4c. **Snapshot analysis service** - compose schema diff and risk rules into one result for later Admin and CLI features.
- [x] 5. **Code-usage scanner architecture** - define exchangeable language strategies and a durable code-reference model without a regex-only parser shortcut.
- [x] 6. **PHP ACF usage scanner** - scan supported PHP ACF call sites and connect references to changed fields.

## Developer experience

- [x] 7. **WordPress Admin foundation** - add the ACF Schema Guard menu and minimal Overview, Changes, Field Groups, Code Usage, History, and Settings screens.
- [x] 8. **WP-CLI analysis commands**
  - [x] 8a. **WP-CLI command foundation and scan** - register `wp acf-schema-guard scan`, accept explicit source roots, and report deterministic PHP ACF usage references.
  - [x] 8b. **WP-CLI snapshot diff** - compare two stored snapshots and report schema changes with risk classifications.
  - [x] 8c. **WP-CLI breaking-change check** - expose a CI-oriented check with `--fail-on-breaking` and a stable non-zero exit status.
- [x] 9. **CI integration guidance and verification** - provide GitHub Actions and GitLab CI examples around the WP-CLI check command.

## Admin workspace

- [x] 10. **Admin snapshot workspace**
  - [x] 10a. **Snapshot history and capture** - show stored snapshots in the History screen and let an administrator capture the current ACF schema with capability and nonce protection.
  - [x] 10b. **Snapshot comparison** - let an administrator select two stored snapshots and show classified schema changes in the Changes screen.

## Future enhancements

- [ ] 11. **Comprehensive schema change explainer** - describe added, removed, and modified field groups and fields with readable before-to-after details for all supported normalized schema properties.
  - [x] 11a. **Core change explainer** - produce deterministic, readable descriptions for added, removed, and modified field groups and fields, including core identity properties.
  - [ ] 11b. **Settings and nested-structure explanations** - explain supported settings, location and conditional rules, repeater/group sub-fields, and Flexible Content layouts.
    - [x] 11b1. **Settings and rule explanations** - explain before-to-after changes to field-group location, field conditional logic, and individual normalized ACF settings.
    - [ ] 11b2. **Nested-structure explanations** - explain added, removed, and modified repeater/group sub-fields and Flexible Content layouts without duplicating child findings.
  - [ ] 11c. **Shared explanation output** - use one explanation service consistently in Admin change views and WP-CLI JSON/table output.
  - [ ] 11d. **Plugin Admin change visual system** - make only ACF Schema Guard Admin change views easier to scan with accessible severity colours, text labels, a legend, and readable grouping; colour must never be the only risk signal.

## Deployment workflow

- [x] 12. **Baseline comparison workflow**
  - [x] 12a. **Admin baseline workflow** - let an administrator mark a stored snapshot as the approved baseline, capture current schema, and automatically compare baseline to current without selecting UUID pairs.
  - [x] 12b. **Git baseline and CI workflow** - export or import a versioned baseline JSON file and add WP-CLI commands that compare the checkout schema to it with CI-safe failure status.
