<?php
/**
 * Asserts that Admin snapshot views use bounded repository reads.
 *
 * @package ACFSchemaGuard
 */

define( 'ABSPATH', __DIR__ . '/' );

$acf_schema_guard_options = array();

function __( $text ) { return $text; }
function _n( $single, $plural, $number ) { return 1 === (int) $number ? $single : $plural; }
function esc_html__( $text ) { return esc_html( $text ); }
function esc_attr__( $text ) { return esc_attr( $text ); }
function esc_html( $text ) { return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' ); }
function esc_attr( $text ) { return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' ); }
function esc_url( $url ) { return (string) $url; }
function admin_url( $path = '' ) { return '/wp-admin/' . ltrim( $path, '/' ); }
function wp_nonce_field() {}
function submit_button( $text ) { echo esc_html( $text ); }
function selected( $selected, $current ) { return $selected === $current ? 'selected="selected"' : ''; }
function sanitize_text_field( $value ) { return trim( (string) $value ); }
function wp_unslash( $value ) { return $value; }
function trailingslashit( $value ) { return rtrim( $value, '/\\' ) . '/'; }
function get_stylesheet_directory() { return dirname( __DIR__, 4 ) . '/wp-content/themes/acf-schema-guard-dev'; }
function update_option( $key, $value ) { global $acf_schema_guard_options; $acf_schema_guard_options[ $key ] = $value; return true; }
function get_option( $key, $default = false ) { global $acf_schema_guard_options; return isset( $acf_schema_guard_options[ $key ] ) ? $acf_schema_guard_options[ $key ] : $default; }

require_once dirname( __DIR__ ) . '/includes/snapshots/class-schema-snapshot.php';
require_once dirname( __DIR__ ) . '/includes/snapshots/interface-snapshot-repository.php';
require_once dirname( __DIR__ ) . '/includes/snapshots/class-baseline-snapshot-service.php';
require_once dirname( __DIR__ ) . '/includes/acf/class-source-health-finding.php';
require_once dirname( __DIR__ ) . '/includes/acf/class-source-health-report.php';
require_once dirname( __DIR__ ) . '/includes/scanner/class-code-usage-reference.php';
require_once dirname( __DIR__ ) . '/includes/scanner/interface-code-usage-scanner.php';
require_once dirname( __DIR__ ) . '/includes/scanner/class-code-usage-scanner-service.php';
require_once dirname( __DIR__ ) . '/includes/scanner/class-current-code-usage-service.php';
require_once dirname( __DIR__ ) . '/includes/scanner/class-php-acf-usage-scanner.php';
require_once dirname( __DIR__ ) . '/includes/scanner/class-scanner-configuration.php';
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
	static function () {
		return new class() {
			public function to_array() {
				return array(
					'findings' => array(
						array(
							'change' => array(
								'kind'      => 'modified',
								'node_type' => 'field',
								'path'      => array( 'group_example', 'field_example' ),
								'before'    => array( 'name' => 'hero_title' ),
								'after'     => array( 'name' => 'hero_heading' ),
							),
							'severity'    => 'high',
							'rationale'   => 'Field name changed.',
							'explanation' => array( 'summary' => 'Field modified.', 'details' => array() ),
						),
					),
				);
			}
		};
	},
	$baseline,
	static function () { return new \AcfSchemaGuard\Acf\SourceHealthReport( true, array( new \AcfSchemaGuard\Acf\SourceHealthFinding( 'group_example', 'Example group', 'divergent', array( 'key' => 'group_example' ), array( 'key' => 'group_example' ) ) ) ); },
	static function ( array $changes ) {
		return array(
			new class( $changes[0] ) {
				private $change;
				public function __construct( array $change ) { $this->change = $change; }
				public function to_array() {
					return array(
						'change'    => $this->change,
						'reference' => array( 'path' => 'template-parts/acf/hero.php', 'line' => 12, 'expression' => "get_field('hero_title')" ),
						'severity'  => 'high',
					);
				}
			}
		);
	},
	static function () use ( $baseline_snapshot ) {
		return new class( $baseline_snapshot ) {
			private $baseline;

			public function __construct( $baseline ) {
				$this->baseline = $baseline;
			}

			public function is_available() { return true; }
			public function baseline() { return $this->baseline; }
			public function message() { return ''; }
			public function analysis() {
				return new class() {
					public function to_array() {
						return array(
							'findings' => array(
								array(
									'change' => array( 'kind' => 'modified', 'node_type' => 'field', 'path' => array( 'group_example', 'field_example' ), 'before' => array( 'name' => 'hero_title' ), 'after' => array( 'name' => 'hero_heading' ) ),
									'severity' => 'high',
									'rationale' => 'Field name changed.',
									'explanation' => array( 'summary' => 'Field modified.', 'details' => array() ),
								),
							),
						);
					}
				};
			}
		};
	}
);
$reflection = new ReflectionClass( $controller );

foreach ( array( 'comparison_selection', 'requested_snapshot_id', 'render_snapshot_options', 'render_comparison_notice' ) as $obsolete_method ) {
	acf_schema_guard_admin_snapshot_assert( ! $reflection->hasMethod( $obsolete_method ), 'Obsolete manual comparison method remains: ' . $obsolete_method );
}

$history = $reflection->getMethod( 'render_history_page' );
$history->setAccessible( true );
$changes = $reflection->getMethod( 'render_changes_page' );
$changes->setAccessible( true );
$source_health = $reflection->getMethod( 'render_source_health_page' );
$source_health->setAccessible( true );
$code_usage = $reflection->getMethod( 'render_code_usage_page' );
$code_usage->setAccessible( true );

ob_start();
$source_health->invoke( $controller, array( 'title' => 'Field Groups' ) );
$source_health_output = ob_get_clean();

acf_schema_guard_admin_snapshot_assert( false !== strpos( $source_health_output, 'Divergent' ), 'Source health status is not rendered.' );
acf_schema_guard_admin_snapshot_assert( false !== strpos( $source_health_output, 'Do not overwrite either source blindly.' ), 'Source health guidance is not rendered.' );
$screen = array( 'title' => 'History', 'description' => '' );

ob_start();
$history->invoke( $controller, $screen );
$history_output = ob_get_clean();
acf_schema_guard_admin_snapshot_assert( false !== strpos( $history_output, $current_snapshot->id() ), 'History did not render the newest snapshot.' );
acf_schema_guard_admin_snapshot_assert( array( 25 ) === $repository->recent_limits, 'History did not request the bounded snapshot list.' );

ob_start();
$changes->invoke( $controller, array( 'title' => 'Changes', 'description' => '' ) );
$changes_output = ob_get_clean();
acf_schema_guard_admin_snapshot_assert( false !== strpos( $changes_output, 'current live schema' ), 'Changes did not render the live schema label.' );
acf_schema_guard_admin_snapshot_assert( false !== strpos( $changes_output, 'not saved as a snapshot' ), 'Changes did not explain that live schema is not persisted.' );
acf_schema_guard_admin_snapshot_assert( false !== strpos( $changes_output, 'Affected code references' ), 'Changes did not render code impacts.' );
acf_schema_guard_admin_snapshot_assert( false !== strpos( $changes_output, 'template-parts/acf/hero.php:12' ), 'Changes did not render an affected code location.' );
acf_schema_guard_admin_snapshot_assert( 0 === $repository->latest_calls, 'Changes must not request the newest snapshot.' );
acf_schema_guard_admin_snapshot_assert( 0 === $repository->all_calls, 'Admin requested the full snapshot collection.' );

$_GET = array();
ob_start();
$code_usage->invoke( $controller, array( 'title' => 'Code Usage' ) );
$code_usage_output = ob_get_clean();
acf_schema_guard_admin_snapshot_assert( false !== strpos( $code_usage_output, 'card_title' ), 'Code Usage did not render a scanned field.' );
acf_schema_guard_admin_snapshot_assert( false !== strpos( $code_usage_output, 'template-parts/acf/card.php' ), 'Code Usage did not render a reference path.' );
acf_schema_guard_admin_snapshot_assert( false !== strpos( $code_usage_output, 'get_field' ), 'Code Usage did not render the ACF expression.' );
acf_schema_guard_admin_snapshot_assert( false !== strpos( $code_usage_output, 'configured themes and plugins as they exist now' ), 'Code Usage did not identify the current configured roots.' );

$_GET = array( 'acf_schema_guard_field' => 'card_title' );
ob_start();
$code_usage->invoke( $controller, array( 'title' => 'Code Usage' ) );
$filtered_code_usage_output = ob_get_clean();
acf_schema_guard_admin_snapshot_assert( false !== strpos( $filtered_code_usage_output, 'card_title' ), 'Code Usage field filter did not retain its field.' );
acf_schema_guard_admin_snapshot_assert( false === strpos( $filtered_code_usage_output, '<code>hero_cta</code>' ), 'Code Usage field filter did not narrow the results.' );

echo "Admin snapshot query assertions passed.\n";
