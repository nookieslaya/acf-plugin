# Current Feature

> **Generated file.** Holds the one feature, fix, or rollback being built right now. Run
> `/feature <number-or-name>` to spec a build-plan feature, or `/fix "<bug>"` for
> an ad-hoc fix. Use `/rollback <completed-feature>` to plan a safe reversal.
> Build one thing at a time; `/complete` archives it under
> `blueprint/history/` and resets this file.

# Current Feature

> **Generated file.** Holds the one feature, fix, or rollback being built right now.

_Nothing in progress. Run `/feature`, `/fix`, or `/rollback` to start._

## Goal

Classify deterministic schema changes through extensible rules as safe, warning,
high, or critical without coupling rules to storage or UI.

## Build steps

- [ ] Add risk finding and rule contracts.
- [ ] Classify baseline ACF changes and add assertions.
- [ ] Expose the classifier through the plugin service and document it.
