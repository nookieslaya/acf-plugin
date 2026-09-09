<?php
define( 'ABSPATH', __DIR__ . '/' );
function wp_json_encode( $value ) { return json_encode( $value ); }
require_once dirname( __DIR__ ) . '/includes/snapshots/class-schema-snapshot.php';
require_once dirname( __DIR__ ) . '/includes/snapshots/interface-snapshot-repository.php';
require_once dirname( __DIR__ ) . '/includes/snapshots/class-automatic-snapshot-service.php';
$schema = array( 'schema_version' => 1, 'field_groups' => array() );
$snapshot = new \AcfSchemaGuard\Snapshots\SchemaSnapshot( '123e4567-e89b-12d3-a456-426614174000', 'acf-auto', $schema, '2026-09-09 00:00:00' );
$repository = new class( $snapshot ) implements \AcfSchemaGuard\Snapshots\SnapshotRepository { private $snapshot; public function __construct( $snapshot ) { $this->snapshot = $snapshot; } public function insert( \AcfSchemaGuard\Snapshots\SchemaSnapshot $snapshot ) {} public function find( $id ) { return null; } public function latest_for_source( $source ) { return \AcfSchemaGuard\Snapshots\AutomaticSnapshotService::SOURCE_ID === $source ? $this->snapshot : null; } public function latest() { return null; } public function recent( $limit ) { return array(); } public function all() { return array(); } };
$service = new \AcfSchemaGuard\Snapshots\AutomaticSnapshotService( $repository );
if ( $service->needs_capture( $schema ) || ! $service->needs_capture( array( 'schema_version' => 1, 'field_groups' => array( array( 'key' => 'changed' ) ) ) ) ) { exit( 1 ); }
echo "Automatic snapshot service assertions passed.\n";
