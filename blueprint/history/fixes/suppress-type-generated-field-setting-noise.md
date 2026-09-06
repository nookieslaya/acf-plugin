# Fix: Suppress type-generated field setting noise

**Type:** Fix
**Status:** verified

## Summary

ACF type transitions can write technical and type-specific defaults that look
like manual schema edits. `_name` is now excluded from normalized settings. The
shared change explainer suppresses `_name`, `allow_in_bindings`, `append`,
`prepend`, `new_lines`, and `rows` only when a field type changes. Field name,
label, type, and other core changes remain visible; same-type settings changes
remain fully detailed.

## Verification

- Full PHP lint passed for the plugin and development theme.
- All 18 assertion scripts passed.
- Focused assertions cover a name plus type transition and same-type `rows`
  changes.
