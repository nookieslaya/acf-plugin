# Feature: Code-usage scanner architecture

**From build-plan:** feature 5
**Status:** complete

## Goal

Define pluggable, storage-independent contracts for scanners that report ACF
field references from project source code. This creates the boundary for the
later PHP scanner without implementing a regex-only parser.

## In scope

- Immutable `CodeUsageReference` with field name, strategy, path, line, and expression.
- `CodeUsageScanner` strategy interface and aggregate scanner service.
- Deterministic source-root traversal boundary, injected rather than coupled to WordPress or a filesystem implementation.
- Deduplication and stable ordering of references across multiple strategies.
- Isolated assertions using fake strategies, without scanning the real project.

## Out of scope

- Parsing PHP, Blade, Twig, Timber, JavaScript, or custom wrappers.
- Regular-expression matching, AST dependencies, filesystem writes, Admin UI,
  CLI commands, persistence, or linking references to schema changes.

## Build steps

- [x] **Step 1 - Usage reference and scanner contracts** - add immutable reference and scanner interfaces. *Done when:* a strategy can return validated references without depending on parser or filesystem details.
- [x] **Step 2 - Aggregate scanner service** - combine strategies with stable ordering and deduplication. *Done when:* fake scanners produce one deterministic result set and one strategy failure is explicit.
- [x] **Step 3 - Plugin seam and verification** - expose scanner composition for later PHP scanning and add durable assertions. *Done when:* feature 6 can register one PHP strategy without changing the aggregate service.

## Testing

Use isolated PHP assertions for validation, ordering, deduplication, and fake strategy composition. Run `php -l` for plugin PHP files.

## Notes for the AI

- PHP 7.4 compatible and no new dependencies.
- Do not implement a parser in this feature. Feature 6 owns supported PHP call-site detection.
- Paths are project-relative strings; line numbers are positive integers.
