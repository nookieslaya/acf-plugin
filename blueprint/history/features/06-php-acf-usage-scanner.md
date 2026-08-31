# Feature: PHP ACF usage scanner

**From build-plan:** feature 6
**Status:** complete

## Goal

Implement one PHP scanner strategy that reports supported ACF field references
from PHP source files through the scanner architecture created in feature 5.

## In scope

- A `PhpAcfUsageScanner` implementing `CodeUsageScanner`.
- Recursive scanning of caller-provided, project-relative PHP source roots.
- Supported direct calls with a literal first argument: `get_field()`,
  `the_field()`, `get_sub_field()`, `the_sub_field()`, `have_rows()`, and
  `get_field_object()`.
- `CodeUsageReference` values with field name, strategy `php-acf`, relative
  path, exact 1-based line, and matched expression.
- Deterministic ordering, deduplication through the existing aggregate service,
  unreadable-file safety, and isolated fixture assertions.

## Out of scope

- Dynamic expressions, variables, constants, concatenation, array access,
  object or static wrappers, namespaced wrapper functions, Blade/Twig/JS,
  AST dependencies, PHP execution, changes to source files, persistence, UI,
  CLI, or linking references to schema findings.

## Build steps

- [x] **Step 1 - PHP scanner boundary** - add a scanner strategy and safe file discovery for supplied roots. *Done when:* only readable `.php` files under existing roots are inspected and no file is executed.
- [x] **Step 2 - Supported literal ACF calls** - detect the supported direct call forms with literal field names and exact lines. *Done when:* fixtures report the correct references and skip dynamic calls.
- [x] **Step 3 - Plugin seam and deterministic verification** - expose the PHP strategy for future composition and add fixture assertions plus PHP lint. *Done when:* a future Admin or CLI feature can pass roots to one strategy without changing scanner architecture.

## Testing

Use isolated PHP fixture files covering each supported function, duplicates,
multiple lines, nested directories, dynamic values, unreadable or missing roots,
and stable output. Run `php -l` for all plugin PHP files.

## Notes for the AI

- PHP 7.4 compatible, no external parser dependency, and no PHP `include`/eval.
- Treat source roots as caller-controlled paths: normalize them, require they
  exist and are directories, and never traverse outside a provided root.
- Match only literal single- or double-quoted first arguments. Omitting dynamic
  calls is correct and must be documented.
