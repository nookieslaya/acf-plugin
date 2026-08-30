# Feature: Risk classification rules

**From build-plan:** feature 4b
**Status:** complete

## Delivered

- `RiskFinding` stores a diff change, severity, and rationale.
- `RiskClassifier` classifies additions as safe, removals as critical, field type changes as high, and other modifications as warnings.
- The plugin loads the rule classes for later analysis orchestration.

## Verification

`php -l` passed for all diff classes.
