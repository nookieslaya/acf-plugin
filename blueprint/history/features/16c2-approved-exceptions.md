# Feature: Approved exceptions

**From build-plan:** feature 16c2
**Status:** verified

## Goal

Let a Pro team explicitly accept one exact schema finding for a bounded period,
without hiding it or changing the underlying schema analysis.

## Delivered

- Deterministic fingerprints bind an exception to a specific schema finding.
- Pro-only Admin actions require a reason, record the current WordPress user,
  support optional expiry, and allow revocation.
- Changes preserves the original severity and rationale while visibly presenting
  an active approval.
- `check` and `baseline check` ignore only an active exact-match exception when
  evaluating `--fail-on-breaking`.
- Matching English and Polish operational guidance explains the workflow,
  boundaries, and team-review use.

## Data / contracts

- Records are bounded plugin-owned WordPress options and never modify ACF,
  snapshots, Local JSON, post meta, or repository files.
- An exception stores its fingerprint, reason, creator identity, creation time,
  and optional expiry time.
- Expired, revoked, malformed, or non-matching records do not bypass the active
  release policy.

## Testing

- Focused approved-exception assertions.
- `sh wp-content/plugins/acf-schema-guard/tests/run-assertions.sh` passed.
