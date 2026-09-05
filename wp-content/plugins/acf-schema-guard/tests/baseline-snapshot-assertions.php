<?php
define( 'ABSPATH', __DIR__ . '/' );
$acf_schema_guard_options = array();
function update_option( $key, $value, $autoload = null ) { global $acf_schema_guard_options; $acf_schema_guard_options[ $key ] = $value; return true; }
function get_option( $key, $default = false ) { global $acf_schema_guard_options; return isset( $acf_schema_guard_options[ $key ] ) ? $acf_schema_guard_options[ $key ] : $default; }
require_once dirname( __DIR__ ) . '/includes/snapshots/class-schema-snapshot.php';
require_once dirname( __DIR__ ) . '/includes/snapshots/interface-snapshot-repository.php';
require_once dirname( __DIR__ ) . '/includes/snapshots/class-baseline-snapshot-service.php';
$snapshot = new \AcfSchemaGuard\Snapshots\SchemaSnapshot( '123e4567-e89b-12d3-a456-426614174000', 'test', array( 'schema_version' => 1, 'field_groups' => array() ), '2026-09-05 12:00:00' );
$repository = new class( $snapshot ) implements \AcfSchemaGuard\Snapshots\SnapshotRepository { private $snapshot; public function __construct( $snapshot ) { $this->snapshot = $snapshot; } public function insert( \AcfSchemaGuard\Snapshots\SchemaSnapshot $snapshot ) {} public function find( $id ) { return $this->snapshot->id() === $id ? $this->snapshot : null; } public function latest_for_source( $source_id ) { return null; } public function all() { return array( $this->snapshot ); } };
$baseline = new \AcfSchemaGuard\Snapshots\BaselineSnapshotService( $repository );
if ( null !== $baseline->snapshot() ) { exit( 1 ); }
$baseline->set( $snapshot );
if ( $snapshot->id() !== $baseline->snapshot()->id() ) { exit( 1 ); }
update_option( \AcfSchemaGuard\Snapshots\BaselineSnapshotService::OPTION_NAME, 'stale-id', false );
if ( null !== $baseline->snapshot() ) { exit( 1 ); }
echo "Baseline snapshot assertions passed.\n";
