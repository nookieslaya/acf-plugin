<?php
/**
 * Asserts Plugin composition for the read-only live baseline workflow.
 *
 * Run with: php tests/plugin-live-baseline-assertions.php
 */

define( 'ABSPATH', __DIR__ . '/' );
define( 'ACF_SCHEMA_GUARD_PATH', dirname( __DIR__ ) . '/' );

$acf_schema_guard_options = array();
$acf_schema_guard_groups  = array(
	array(
		'key'    => 'group_example',
		'title'  => 'Example',
		'fields' => array( array( 'key' => 'field_example', 'name' => 'example', 'type' => 'text' ) ),
	),
);

function get_option( $key, $default = false ) {
	global $acf_schema_guard_options;

	return isset( $acf_schema_guard_options[ $key ] ) ? $acf_schema_guard_options[ $key ] : $default;
}

function update_option( $key, $value ) {
	global $acf_schema_guard_options;
	$acf_schema_guard_options[ $key ] = $value;

	return true;
}

function acf_get_field_groups() {
	global $acf_schema_guard_groups;

	return $acf_schema_guard_groups;
}

function acf_get_fields( $group ) {
	return isset( $group['fields'] ) ? $group['fields'] : array();
}

require_once dirname( __DIR__ ) . '/includes/class-plugin.php';

function acf_schema_guard_plugin_live_assert( $condition, $message ) {
	if ( ! $condition ) {
		throw new RuntimeException( $message );
	}
}

$plugin     = \AcfSchemaGuard\Plugin::instance();
$baseline   = new \AcfSchemaGuard\Snapshots\SchemaSnapshot(
	'123e4567-e89b-12d3-a456-426614174000',
	'manual',
	$plugin->current_schema_array(),
	'2026-09-09 12:00:00'
);
$repository = new class( $baseline ) implements \AcfSchemaGuard\Snapshots\SnapshotRepository {
	public $insert_calls = 0;
	private $baseline;

	public function __construct( $baseline ) {
		$this->baseline = $baseline;
	}

	public function insert( \AcfSchemaGuard\Snapshots\SchemaSnapshot $snapshot ) {
		$this->insert_calls++;
	}

	public function find( $id ) {
		return $this->baseline->id() === $id ? $this->baseline : null;
	}

	public function latest_for_source( $source_id ) { return null; }
	public function latest() { return null; }
	public function recent( $limit ) { return array(); }
	public function all() { return array(); }
};

$reflection = new ReflectionClass( $plugin );
$property   = $reflection->getProperty( 'snapshot_repository' );
$property->setAccessible( true );
$property->setValue( $plugin, $repository );
update_option( \AcfSchemaGuard\Snapshots\BaselineSnapshotService::OPTION_NAME, $baseline->id() );

$acf_schema_guard_groups[0]['fields'][0]['type'] = 'textarea';
$live = $plugin->analyze_live_baseline();

acf_schema_guard_plugin_live_assert( $live->is_available(), 'Plugin live comparison should be available.' );
acf_schema_guard_plugin_live_assert( 'high' === $live->analysis()->to_array()['findings'][1]['severity'], 'Plugin live comparison should classify a type change.' );
acf_schema_guard_plugin_live_assert( 0 === $repository->insert_calls, 'Plugin live comparison must not create a snapshot.' );
acf_schema_guard_plugin_live_assert( $baseline->id() === get_option( \AcfSchemaGuard\Snapshots\BaselineSnapshotService::OPTION_NAME ), 'Plugin live comparison must not update the baseline.' );

echo "Plugin live baseline assertions passed.\n";
