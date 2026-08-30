# Feature: Snapshot capture service

**From build-plan:** feature 3c
**Status:** complete

## Goal

Add one explicit service that composes ACF runtime discovery, schema
normalization, and append-only snapshot persistence. A later Admin screen or
WP-CLI command can ask it to capture a named source without knowing ACF or
database details.

## In scope

- A `SnapshotCaptureService` that depends on the existing ACF environment
  provider, full-schema source, normalizer, and snapshot repository.
- `capture( $source_id )` creates a UUID and UTC timestamp, normalizes the
  current ACF runtime, persists exactly one immutable snapshot, and returns it.
- An inactive ACF runtime raises a clear exception before any database insert.
- A running ACF instance with zero groups creates a valid empty normalized
  snapshot.
- A lazy plugin seam: `$plugin->capture_snapshot( $source_id )`.
- Isolated assertions that prove the complete capture path, the no-ACF failure
  path, and empty-schema behavior, plus PHP syntax validation.

## Out of scope

- Automatic capture on activation, plugin boot, cron, save hooks, or requests.
- Admin actions, WP-CLI, REST, retention, snapshot comparison, hashes,
  checksums, source auto-detection, or choosing a default source ID.
- Table installation changes, update/delete repository methods, ACF internal
  APIs, or direct Local JSON parsing.

## Build loop

Build one small reviewed step at a time. Capture is write-capable, so no step
may invoke it automatically in a real WordPress request.

## Build steps

- [x] **Step 1 - Capture service contract** - add the service and its injected
  boundaries for environment discovery, full-schema source, normalization, and
  persistence. *Done when:* active ACF input creates one snapshot with a UUID,
  UTC creation time, canonical schema, and caller-provided source ID.
- [x] **Step 2 - Failure and empty-schema paths** - make unavailable ACF fail
  before persistence while an available runtime with no groups persists a valid
  empty schema. *Done when:* isolated fakes prove zero inserts on the failure
  path and exactly one insert for an active empty schema.
- [x] **Step 3 - Plugin seam and deterministic verification** - expose explicit
  capture through the plugin service, document it, and add a durable assertion
  script plus `php -l`. *Done when:* a later Admin or CLI feature can call one
  method with a source ID, while plugin boot itself writes nothing.

## Files / areas

- `wp-content/plugins/acf-schema-guard/includes/snapshots/`
- `wp-content/plugins/acf-schema-guard/includes/class-plugin.php`
- `wp-content/plugins/acf-schema-guard/readme.md`
- `wp-content/plugins/acf-schema-guard/tests/`

## Data / contracts

This service owns orchestration only. `SchemaSnapshot`, `SnapshotRepository`,
the table schema, `AcfSchemaSource`, and `SchemaNormalizer` remain the contracts
defined by features 3a and 3b.

### `SnapshotCaptureService::capture( $source_id )`

- Accepts a non-empty caller-provided source ID, which is validated by
  `SchemaSnapshot`.
- Requires `AcfEnvironmentProvider::discover()->is_available()` to be true.
  Otherwise it throws `RuntimeException` before calling the repository.
- Reads full field groups through `FullSchemaSource::field_groups()`, normalizes
  them, creates `SchemaSnapshot( wp_generate_uuid4(), $source_id, ...,
  gmdate( 'Y-m-d H:i:s' ) )`, inserts it once, and returns it.
- The same source ID may be captured repeatedly: each call creates a new,
  immutable row. It never overwrites the prior snapshot.
- No default source ID exists. A future UI or CLI selects an explicit value such
  as `acf-runtime`.

## Testing

No project test runner is configured. Add standalone PHP assertions with fake
environment, source, and repository boundaries to verify:

- active ACF captures nested normalized data into exactly one snapshot;
- inactive ACF throws and produces zero inserts;
- active ACF with no field groups captures a schema with `field_groups: []`;
- source ID, UUID format, and UTC timestamp are populated;
- plugin boot remains read-only until `capture_snapshot()` is called.

Run `php -l` for every plugin PHP file and all existing assertion scripts. The
local CLI database limitation does not block this service-level verification;
real table writes remain a manual post-implementation check.

## Notes for the AI

- Use PHP 7.4-compatible syntax, public ACF APIs only, and no new dependencies.
- Keep the service injectable for isolated tests. Do not duplicate repository
  serialization or ACF normalization logic.
- Use `wp_generate_uuid4()` and `gmdate()` only when capture is explicitly
  called, never at plugin boot.
- Let repository errors propagate. Do not catch an insert failure and claim a
  snapshot was captured.
- This feature writes data only when a caller invokes capture. No forms,
  nonces, capabilities, or output are introduced until an Admin or CLI feature
  gives that operation a user-facing entry point.
