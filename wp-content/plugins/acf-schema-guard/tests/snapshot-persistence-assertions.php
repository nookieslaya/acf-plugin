<?php
/**
 * Isolated assertions for snapshot persistence contracts.
 *
 * @package ACFSchemaGuard
 */

define( 'ABSPATH', __DIR__ . '/' );
define( 'ACF_SCHEMA_GUARD_PATH', dirname( __DIR__ ) . '/' );
define( 'ARRAY_A', 'ARRAY_A' );

$wpdb = new class {
	public $prefix = 'wp_';
	public $last_error = '';
	public $rows = array();
	public $inserted_table = '';
	public $last_results_query = '';

	public function get_charset_collate() {
		return 'DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci';
	}

	public function insert( $table, $data, $formats ) {
		$this->inserted_table = $table;
		$this->rows[]         = $data;

		return 1;
	}

	public function prepare( $query, $value ) {
		if ( false !== strpos( $query, '%d' ) ) {
			return sprintf( $query, (int) $value );
		}

		return $query;
	}

	public function get_row( $query, $format ) {
		if ( false !== strpos( $query, 'WHERE id' ) ) {
			return isset( $this->rows[0] ) ? $this->rows[0] : null;
		}

		return isset( $this->rows[ count( $this->rows ) - 1 ] ) ? $this->rows[ count( $this->rows ) - 1 ] : null;
	}

	public function get_results( $query, $format ) {
		$this->last_results_query = $query;

		$rows = array_reverse( $this->rows );

		if ( preg_match( '/LIMIT (\d+)/', $query, $matches ) ) {
			return array_slice( $rows, 0, (int) $matches[1] );
		}

		return $rows;
	}
};

require_once dirname( __DIR__ ) . '/includes/class-plugin.php';

$snapshot = new \AcfSchemaGuard\Snapshots\SchemaSnapshot(
	'123e4567-e89b-12d3-a456-426614174000',
	'acf-runtime',
	array( 'schema_version' => 1, 'field_groups' => array() ),
	'2026-08-30 12:34:56'
);
$repository = \AcfSchemaGuard\Plugin::instance()->snapshot_repository();
$repository->insert( $snapshot );
$wpdb->rows[] = array(
	'id'             => '123e4567-e89b-12d3-a456-426614174001',
	'source_id'      => 'admin-manual',
	'schema_version' => 1,
	'schema'         => json_encode( array( 'schema_version' => 1, 'field_groups' => array() ) ),
	'created_at'     => '2026-08-31 12:34:56',
);
$found = $repository->find( $snapshot->id() );
$all   = $repository->all();
$recent = $repository->recent( 1 );
$latest = $repository->latest();
$sql   = \AcfSchemaGuard\Snapshots\SnapshotTable::create_table_sql( $wpdb );

if (
	'wp_acf_schema_guard_snapshots' !== $wpdb->inserted_table ||
	$found->to_row() !== $snapshot->to_row() ||
	2 !== count( $all ) ||
	'123e4567-e89b-12d3-a456-426614174001' !== $all[0]->id() ||
	1 !== count( $recent ) ||
	'123e4567-e89b-12d3-a456-426614174001' !== $recent[0]->id() ||
	'123e4567-e89b-12d3-a456-426614174001' !== $latest->id() ||
	false === strpos( $wpdb->last_results_query, 'LIMIT 1' ) ||
	false === strpos( $wpdb->last_results_query, 'ORDER BY created_at DESC, id DESC' ) ||
	false === strpos( $sql, 'PRIMARY KEY  (id)' ) ||
	false === strpos( $sql, 'KEY source_created (source_id, created_at)' )
) {
	fwrite( STDERR, "Snapshot persistence assertion failed.\n" );
	exit( 1 );
}

try {
	$repository->recent( 0 );
	fwrite( STDERR, "Snapshot history limit assertion failed.\n" );
	exit( 1 );
} catch ( \RuntimeException $exception ) {
	if ( false === strpos( $exception->getMessage(), 'Snapshot history limit' ) ) {
		throw $exception;
	}
}

echo "Snapshot persistence assertions passed.\n";
