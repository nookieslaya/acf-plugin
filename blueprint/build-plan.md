# ACF Schema Guard - Build Plan

## Foundation

- [x] 1. **ACF development test theme** - provide a minimal classic theme with ACF Local JSON, realistic field examples, and documented breaking-change scenarios.
- [x] 2. **Plugin bootstrap and ACF integration boundary** - create the plugin shell and safely discover ACF, ACF PRO, field groups, and Local JSON sources.
- [x] 3. **Schema normalization and snapshots**
  - [x] 3a. **Normalized schema model** - transform ACF discovery data into stable, deterministic schema contracts without persistence.
  - [x] 3b. **Snapshot persistence** - persist and retrieve immutable normalized snapshots in a dedicated WordPress table.
  - [x] 3c. **Snapshot capture service** - compose ACF discovery, normalization, and persistence into explicit snapshot capture.

## Change analysis

- [ ] 4. **Schema diff and extensible risk classification** - compare snapshots and report safe, warning, high, and critical changes without coupling rules to storage.
- [ ] 5. **Code-usage scanner architecture** - define exchangeable language strategies and a durable code-reference model without a regex-only parser shortcut.
- [ ] 6. **PHP ACF usage scanner** - scan supported PHP ACF call sites and connect references to changed fields.

## Developer experience

- [ ] 7. **WordPress Admin foundation** - add the ACF Schema Guard menu and minimal Overview, Changes, Field Groups, Code Usage, History, and Settings screens.
- [ ] 8. **WP-CLI analysis commands** - expose scan, diff, and check commands with a `--fail-on-breaking` exit status.
- [ ] 9. **CI integration guidance and verification** - provide GitHub Actions and GitLab CI examples around the WP-CLI check command.
