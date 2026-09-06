<?php
/**
 * Asserts that Admin snapshot views use bounded repository reads.
 *
 * @package ACFSchemaGuard
 */

define( 'ABSPATH', __DIR__ . '/' );

$acf_schema_guard_options = array();

function __( $text ) { return $text; }
function esc_html__( $text ) { return esc_html( $text ); }
function esc_attr__( $text ) { return esc_attr( $text ); }
function esc_html( $text ) { return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' ); }
function esc_attr( $text ) { return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' ); }
function esc_url( $url ) { return (string) $url; }
function admin_url( $path = '' ) { return '/wp-admin/' . ltrim( $path, '/' ); }
function wp_nonce_field() {}
function submit_button( $text ) { echo esc_html( $text ); }
function update_option( $key, $value ) { global $acf_schema_guard_options; $acf_schema_guard_options[ $key ] = $value; return true; }
function get_option( $key, $default = false ) { global $acf_schema_guard_options; return isset( $acf_schema_guard_options[ $key ] ) ? $acf_schema_guard_options[ $key ] : $default; }

require_once dirname( __DIR__ ) . '/includes/snapshots/class-schema-snapshot.php';
require_once dirname( __DIR__ ) . '/includes/snapshots/interface-snapshot-repository.php';
require_once dirname( __DIR__ ) . '/includes/snapshots/class-baseline-snapshot-service.php';
require_once dirname( __DIR__ ) . '/includes/admin/class-admin-controller.php';

function acf_schema_guard_admin_snapshot_assert( $condition, $message ) {
	if ( ! $condition ) {
		throw new RuntimeException( $message );
	}
}

$baseline_snapshot = new \AcfSchemaGuard\Snapshots\SchemaSnapshot(
	'123e4567-e89b-12d3-a456-426614174000',
	'test',
	array( 'schema_version' => 1, 'field_groups' => array() ),
	'2026-09-01 12:00:00'
);
$current_snapshot = new \AcfSchemaGuard\Snapshots\SchemaSnapshot(
	'123e4567-e89b-12d3-a456-426614174001',
	'test',
	array( 'schema_version' => 1, 'field_groups' => array() ),
	'2026-09-02 12:00:00'
);

$repository = new class( array( $current_snapshot, $baseline_snapshot ) ) implements \AcfSchemaGuard\Snapshots\SnapshotRepository {
	public $recent_limits = array();
	public $latest_calls = 0;
	public $all_calls = 0;
	private $snapshots;

	public function __construct( array $snapshots ) { $this->snapshots = $snapshots; }
	public function insert( \AcfSchemaGuard\Snapshots\SchemaSnapshot $snapshot ) {}
	public function find( $id ) { foreach ( $this->snapshots as $snapshot ) { if ( $snapshot->id() === $id ) { return $snapshot; } } return null; }
	public function latest_for_source( $source_id ) { return $this->latest(); }
	public function latest() { $this->latest_calls++; return $this->snapshots[0]; }
	public function recent( $limit ) { $this->recent_limits[] = $limit; return array_slice( $this->snapshots, 0, $limit ); }
	public function all() { $this->all_calls++; throw new RuntimeException( 'Admin must not load every snapshot.' ); }
};

$baseline = new \AcfSchemaGuard\Snapshots\BaselineSnapshotService( $repository );
$baseline->set( $baseline_snapshot );
$controller = new \AcfSchemaGuard\Admin\AdminController(
	$repository,
	static function () {},
	static function () { return new class() { public function to_array() { return array( 'findings' => array() ); } }; },
	$baseline
);
$reflection = new ReflectionClass( $controller );

foreach ( array( 'comparison_selection', 'requested_snapshot_id', 'render_snapshot_options', 'render_comparison_notice' ) as $obsolete_method ) {
	acf_schema_guard_admin_snapshot_assert( ! $reflection->hasMethod( $obsolete_method ), 'Obsolete manual comparison method remains: ' . $obsolete_method );
}

$history = $reflection->getMethod( 'render_history_page' );
$history->setAccessible( true );
$changes = $reflection->getMethod( 'render_changes_page' );
$changes->setAccessible( true );
$screen = array( 'title' => 'History', 'description' => '' );

ob_start();
$history->invoke( $controller, $screen );
$history_output = ob_get_clean();
acf_schema_guard_admin_snapshot_assert( false !== strpos( $history_output, $current_snapshot->id() ), 'History did not render the newest snapshot.' );
acf_schema_guard_admin_snapshot_assert( array( 25 ) === $repository->recent_limits, 'History did not request the bounded snapshot list.' );

ob_start();
$changes->invoke( $controller, array( 'title' => 'Changes', 'description' => '' ) );
$changes_output = ob_get_clean();
acf_schema_guard_admin_snapshot_assert( false !== strpos( $changes_output, $current_snapshot->id() ), 'Changes did not render the newest snapshot.' );
acf_schema_guard_admin_snapshot_assert( 1 === $repository->latest_calls, 'Changes did not request exactly one newest snapshot.' );
acf_schema_guard_admin_snapshot_assert( 0 === $repository->all_calls, 'Admin requested the full snapshot collection.' );

echo "Admin snapshot query assertions passed.\n";
