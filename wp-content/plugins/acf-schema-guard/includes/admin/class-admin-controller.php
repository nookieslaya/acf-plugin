<?php
/**
 * Registers the read-only WordPress Admin foundation.
 *
 * @package ACFSchemaGuard
 */

namespace AcfSchemaGuard\Admin;

use AcfSchemaGuard\Snapshots\SnapshotRepository;
use AcfSchemaGuard\Snapshots\BaselineSnapshotService;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class AdminController {
	/** @var int */
	const HISTORY_SNAPSHOT_LIMIT = 25;

	/**
	 * Read-only Admin screen definitions keyed by page slug.
	 *
	 * @var array<string, array<string, string>>
	 */
	private static $screens = array(
		'acf-schema-guard'              => array(
			'menu_label' => 'Overview',
			'title'      => 'Overview',
			'description' => 'A starting point for reviewing ACF schema safety.',
		),
		'acf-schema-guard-changes'      => array(
			'menu_label' => 'Changes',
			'title'      => 'Changes',
			'description' => 'Schema changes will appear here after a comparison is run.',
		),
		'acf-schema-guard-field-groups' => array(
			'menu_label' => 'Field Groups',
			'title'      => 'Field Groups',
			'description' => 'Normalized field groups will appear here in a later feature.',
		),
		'acf-schema-guard-code-usage'   => array(
			'menu_label' => 'Code Usage',
			'title'      => 'Code Usage',
			'description' => 'References found by supported code scanners will appear here.',
		),
		'acf-schema-guard-history'      => array(
			'menu_label' => 'History',
			'title'      => 'History',
			'description' => 'Captured schema snapshots will appear here in a later feature.',
		),
		'acf-schema-guard-settings'     => array(
			'menu_label' => 'Settings',
			'title'      => 'Settings',
			'description' => 'Configuration controls will appear here when settings are supported.',
		),
	);

	/**
	 * Required capability for every plugin Admin page.
	 *
	 * @var string
	 */
	private $capability = 'manage_options';

	/**
	 * WordPress hook suffixes for plugin Admin screens.
	 *
	 * @var string[]
	 */
	private $page_hooks = array();

	/** @var SnapshotRepository */
	private $snapshots;

	/** @var callable */
	private $capture_snapshot_callback;

	/** @var callable */
	private $analyze_snapshots_callback;

	/** @var callable */
	private $source_health_callback;

	/** @var callable */
	private $analyze_code_impact_callback;

	/** @var callable */
	private $analyze_live_baseline_callback;

	/** @var callable */
	private $analyze_stored_data_impact_callback;

	private $baseline;

	/**
	 * @param SnapshotRepository $snapshots                  Stored schema snapshots.
	 * @param callable           $capture_snapshot_callback  Creates a schema snapshot.
	 * @param callable           $analyze_snapshots_callback Analyzes two schema snapshots.
	 * @param callable           $analyze_code_impact_callback Matches changes to code references.
	 */
	public function __construct( SnapshotRepository $snapshots, $capture_snapshot_callback, $analyze_snapshots_callback, BaselineSnapshotService $baseline, $source_health_callback, $analyze_code_impact_callback = null, $analyze_live_baseline_callback = null, $analyze_stored_data_impact_callback = null ) {
		$this->snapshots                  = $snapshots;
		$this->capture_snapshot_callback  = $capture_snapshot_callback;
		$this->analyze_snapshots_callback = $analyze_snapshots_callback;
		$this->source_health_callback     = $source_health_callback;
		$this->analyze_code_impact_callback = $analyze_code_impact_callback;
		$this->analyze_live_baseline_callback = $analyze_live_baseline_callback;
		$this->analyze_stored_data_impact_callback = $analyze_stored_data_impact_callback;
		$this->baseline                   = $baseline;
	}

	/**
	 * Registers the WordPress Admin menu hook.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'admin_menu', array( $this, 'register_menus' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'admin_post_acf_schema_guard_capture_snapshot', array( $this, 'capture_snapshot' ) );
		add_action( 'admin_post_acf_schema_guard_set_baseline_snapshot', array( $this, 'set_baseline_snapshot' ) );
		add_action( 'admin_post_acf_schema_guard_save_scanner_roots', array( $this, 'save_scanner_roots' ) );
	}

	/**
	 * Registers the plugin menu and its read-only section pages.
	 *
	 * @return void
	 */
	public function register_menus() {
		$parent_slug = 'acf-schema-guard';

		$this->page_hooks[] = add_menu_page(
			__( 'ACF Schema Guard', 'acf-schema-guard' ),
			__( 'ACF Schema Guard', 'acf-schema-guard' ),
			$this->capability,
			$parent_slug,
			array( $this, 'render_page' ),
			'dashicons-shield-alt',
			80
		);

		foreach ( self::$screens as $slug => $screen ) {
			$this->page_hooks[] = add_submenu_page(
				$parent_slug,
				__( $screen['title'], 'acf-schema-guard' ),
				__( $screen['menu_label'], 'acf-schema-guard' ),
				$this->capability,
				$slug,
				array( $this, 'render_page' )
			);
		}
	}

	/**
	 * Enqueues styling only for this plugin's Admin screens.
	 *
	 * @param string $hook_suffix Current WordPress Admin hook suffix.
	 * @return void
	 */
	public function enqueue_assets( $hook_suffix ) {
		if ( ! in_array( $hook_suffix, $this->page_hooks, true ) ) {
			return;
		}

		wp_enqueue_style(
			'acf-schema-guard-admin',
			plugins_url( 'assets/css/admin.css', ACF_SCHEMA_GUARD_FILE ),
			array(),
			ACF_SCHEMA_GUARD_VERSION
		);
	}

	/**
	 * Renders the current plugin Admin page without side effects.
	 *
	 * @return void
	 */
	public function render_page() {
		if ( ! current_user_can( $this->capability ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'acf-schema-guard' ) );
		}

		$page   = $this->current_page();
		$screen = $this->current_screen( $page );

		if ( null === $screen ) {
			wp_die( esc_html__( 'The requested ACF Schema Guard page is not available.', 'acf-schema-guard' ) );
		}
		if ( 'acf-schema-guard-history' === $page ) {
			$this->render_history_page( $screen );

			return;
		}

		if ( 'acf-schema-guard-changes' === $page ) {
			$this->render_changes_page( $screen );

			return;
		}

		if ( 'acf-schema-guard-field-groups' === $page ) {
			$this->render_source_health_page( $screen );

			return;
		}
		if ( 'acf-schema-guard-code-usage' === $page ) {
			$this->render_code_usage_page( $screen );
			return;
		}
		if ( 'acf-schema-guard-settings' === $page ) {
			$this->render_settings_page( $screen );
			return;
		}
		if ( 'acf-schema-guard' === $page ) {
			$this->render_overview_page( $screen );
			return;
		}

		?>
		<div class="wrap acf-schema-guard-admin">
			<h1><?php echo esc_html( __( $screen['title'], 'acf-schema-guard' ) ); ?></h1>
			<p><?php echo esc_html( __( $screen['description'], 'acf-schema-guard' ) ); ?></p>
			<div class="notice notice-info inline">
				<p><?php echo esc_html__( 'This read-only section has no data or actions available yet.', 'acf-schema-guard' ); ?></p>
			</div>
		</div>
		<?php
	}

	/**
	 * Renders a read-only starting point for the schema safety workflow.
	 *
	 * @param array<string, string> $screen Screen definition.
	 * @return void
	 */
	private function render_overview_page( array $screen ) {
		$state      = $this->overview_dashboard_state();
		$comparison = $state['comparison'];
		$health     = $state['source_health'];
		?>
		<div class="wrap acf-schema-guard-admin acf-schema-guard-overview-page">
			<h1><?php echo esc_html( __( $screen['title'], 'acf-schema-guard' ) ); ?></h1>
			<p><?php echo esc_html__( 'Review the current ACF schema safety posture, then continue with the workflow that needs attention.', 'acf-schema-guard' ); ?></p>

			<div class="acf-schema-guard-overview-grid">
				<section class="acf-schema-guard-overview-card acf-schema-guard-overview-card-<?php echo esc_attr( $state['baseline']['status'] ); ?>">
					<p class="acf-schema-guard-overview-eyebrow"><?php echo esc_html__( 'Approved baseline', 'acf-schema-guard' ); ?></p>
					<h2><?php echo esc_html( $state['baseline']['label'] ); ?></h2>
					<p><?php echo esc_html( $state['baseline']['description'] ); ?></p>
					<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=acf-schema-guard-history' ) ); ?>"><?php echo esc_html__( 'Open history', 'acf-schema-guard' ); ?></a>
				</section>

				<section class="acf-schema-guard-overview-card acf-schema-guard-overview-card-<?php echo esc_attr( $comparison['status'] ); ?>">
					<p class="acf-schema-guard-overview-eyebrow"><?php echo esc_html__( 'Live schema comparison', 'acf-schema-guard' ); ?></p>
					<h2><?php echo esc_html( $comparison['label'] ); ?></h2>
					<p><?php echo esc_html( $comparison['description'] ); ?></p>
					<?php $this->render_overview_counts( $comparison['counts'], array( 'safe', 'warning', 'high', 'critical' ) ); ?>
					<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=acf-schema-guard-changes' ) ); ?>"><?php echo esc_html__( 'Review changes', 'acf-schema-guard' ); ?></a>
				</section>

				<section class="acf-schema-guard-overview-card acf-schema-guard-overview-card-<?php echo esc_attr( $health['status'] ); ?>">
					<p class="acf-schema-guard-overview-eyebrow"><?php echo esc_html__( 'Database and Local JSON', 'acf-schema-guard' ); ?></p>
					<h2><?php echo esc_html( $health['label'] ); ?></h2>
					<p><?php echo esc_html( $health['description'] ); ?></p>
					<?php $this->render_overview_counts( $health['counts'], array( 'aligned', 'database_only', 'json_only', 'divergent' ) ); ?>
					<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=acf-schema-guard-field-groups' ) ); ?>"><?php echo esc_html__( 'Review field groups', 'acf-schema-guard' ); ?></a>
				</section>
			</div>

			<section class="acf-schema-guard-overview-next-steps">
				<h2><?php echo esc_html__( 'Continue your review', 'acf-schema-guard' ); ?></h2>
				<ul>
					<li><a href="<?php echo esc_url( admin_url( 'admin.php?page=acf-schema-guard-code-usage' ) ); ?>"><?php echo esc_html__( 'Inspect current PHP ACF references', 'acf-schema-guard' ); ?></a></li>
					<li><a href="<?php echo esc_url( admin_url( 'admin.php?page=acf-schema-guard-settings' ) ); ?>"><?php echo esc_html__( 'Choose themes and plugins for code analysis', 'acf-schema-guard' ); ?></a></li>
				</ul>
			</section>
		</div>
		<?php
	}

	/**
	 * @param array<string, int> $counts Count data.
	 * @param string[]           $keys   Ordered count keys.
	 * @return void
	 */
	private function render_overview_counts( array $counts, array $keys ) {
		?>
		<ul class="acf-schema-guard-overview-counts">
			<?php foreach ( $keys as $key ) : ?>
				<li><strong><?php echo esc_html( isset( $counts[ $key ] ) ? $counts[ $key ] : 0 ); ?></strong> <?php echo esc_html( $this->overview_count_label( $key ) ); ?></li>
			<?php endforeach; ?>
		</ul>
		<?php
	}

	/**
	 * @param string $key Count identifier.
	 * @return string
	 */
	private function overview_count_label( $key ) {
		$labels = array(
			'safe'          => __( 'Safe', 'acf-schema-guard' ),
			'warning'       => __( 'Warning', 'acf-schema-guard' ),
			'high'          => __( 'High', 'acf-schema-guard' ),
			'critical'      => __( 'Critical', 'acf-schema-guard' ),
			'aligned'       => __( 'Aligned', 'acf-schema-guard' ),
			'database_only' => __( 'Database only', 'acf-schema-guard' ),
			'json_only'     => __( 'Local JSON only', 'acf-schema-guard' ),
			'divergent'     => __( 'Divergent', 'acf-schema-guard' ),
		);

		return isset( $labels[ $key ] ) ? $labels[ $key ] : $key;
	}

	private function render_settings_page( array $screen ) {
		$roots    = class_exists( '\\AcfSchemaGuard\\Scanner\\ScannerConfiguration' ) ? ( new \AcfSchemaGuard\Scanner\ScannerConfiguration() )->roots() : array();
		$selected = array();
		foreach ( $roots as $root ) {
			$selected[] = realpath( $root );
		}
		?>
		<div class="wrap acf-schema-guard-admin acf-schema-guard-settings-page">
			<h1><?php echo esc_html( $screen['title'] ); ?></h1>
			<p><?php echo esc_html__( 'Choose the themes and plugins to include in code analysis.', 'acf-schema-guard' ); ?></p>
			<form class="acf-schema-guard-settings-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="acf_schema_guard_save_scanner_roots" />
				<?php wp_nonce_field( 'acf_schema_guard_save_scanner_roots' ); ?>
				<section class="acf-schema-guard-settings-card">
					<div class="acf-schema-guard-settings-card-header">
						<p class="acf-schema-guard-overview-eyebrow"><?php echo esc_html__( 'Scanner scope', 'acf-schema-guard' ); ?></p>
						<h2><?php echo esc_html__( 'Include source roots', 'acf-schema-guard' ); ?></h2>
						<p><?php echo esc_html__( 'Only selected locations are scanned for literal PHP ACF calls.', 'acf-schema-guard' ); ?></p>
					</div>
					<div class="acf-schema-guard-root-list">
						<?php foreach ( $this->available_scanner_roots() as $identifier => $label ) : ?>
							<label class="acf-schema-guard-root-option">
								<input type="checkbox" name="scanner_roots[]" value="<?php echo esc_attr( $identifier ); ?>" <?php checked( in_array( realpath( $this->scanner_root_path( $identifier ) ), $selected, true ) ); ?> />
								<span><?php echo esc_html( $label ); ?></span>
							</label>
						<?php endforeach; ?>
					</div>
					<div class="acf-schema-guard-settings-actions">
						<?php submit_button( __( 'Save scanner roots', 'acf-schema-guard' ), 'primary', 'submit', false ); ?>
					</div>
				</section>
			</form>
		</div>
		<?php
	}

	/**
	 * Builds the read-only status data shown by the Overview dashboard.
	 *
	 * @return array<string, mixed>
	 */
	private function overview_dashboard_state() {
		$baseline = $this->baseline->snapshot();
		$live     = is_callable( $this->analyze_live_baseline_callback ) ? call_user_func( $this->analyze_live_baseline_callback ) : null;
		$health   = is_callable( $this->source_health_callback ) ? call_user_func( $this->source_health_callback ) : null;

		return array(
			'baseline'      => $this->overview_baseline_state( $baseline ),
			'comparison'    => $this->overview_comparison_state( $live ),
			'source_health' => $this->overview_source_health_state( $health ),
		);
	}

	/**
	 * @param \AcfSchemaGuard\Snapshots\SchemaSnapshot|null $baseline Approved snapshot.
	 * @return array<string, string>
	 */
	private function overview_baseline_state( $baseline ) {
		if ( null === $baseline ) {
			return array(
				'status'      => 'missing',
				'label'       => __( 'Baseline needed', 'acf-schema-guard' ),
				'description' => __( 'Choose a stored snapshot as the approved baseline before reviewing live changes.', 'acf-schema-guard' ),
			);
		}

		return array(
			'status'      => 'ready',
			'label'       => __( 'Baseline approved', 'acf-schema-guard' ),
			'description' => sprintf( __( 'Captured %s.', 'acf-schema-guard' ), $baseline->created_at() ),
		);
	}

	/**
	 * @param mixed $live Live baseline analysis result.
	 * @return array<string, mixed>
	 */
	private function overview_comparison_state( $live ) {
		$counts = array_fill_keys( array( 'safe', 'warning', 'high', 'critical' ), 0 );

		if ( ! is_object( $live ) || ! method_exists( $live, 'is_available' ) || ! $live->is_available() || ! method_exists( $live, 'analysis' ) ) {
			return array(
				'status'      => 'unavailable',
				'label'       => __( 'Comparison unavailable', 'acf-schema-guard' ),
				'description' => is_object( $live ) && method_exists( $live, 'message' ) ? $live->message() : __( 'A live schema comparison is not available yet.', 'acf-schema-guard' ),
				'counts'      => $counts,
			);
		}

		$analysis = $live->analysis();
		$data     = is_object( $analysis ) && method_exists( $analysis, 'to_array' ) ? $analysis->to_array() : array();
		$findings = isset( $data['findings'] ) && is_array( $data['findings'] ) ? $data['findings'] : array();

		foreach ( $findings as $finding ) {
			if ( is_array( $finding ) && isset( $finding['severity'] ) && array_key_exists( $finding['severity'], $counts ) ) {
				++$counts[ $finding['severity'] ];
			}
		}

		$blocking = $counts['high'] + $counts['critical'];

		return array(
			'status'      => $blocking > 0 ? 'attention' : 'clear',
			'label'       => $blocking > 0 ? __( 'Review required', 'acf-schema-guard' ) : __( 'No high-risk changes', 'acf-schema-guard' ),
			'description' => sprintf( _n( '%d schema finding detected.', '%d schema findings detected.', count( $findings ), 'acf-schema-guard' ), count( $findings ) ),
			'counts'      => $counts,
		);
	}

	/**
	 * @param mixed $report Source-health report.
	 * @return array<string, mixed>
	 */
	private function overview_source_health_state( $report ) {
		$counts = array_fill_keys( array( 'aligned', 'database_only', 'json_only', 'divergent' ), 0 );

		if ( ! is_object( $report ) || ! method_exists( $report, 'is_available' ) || ! $report->is_available() || ! method_exists( $report, 'findings' ) ) {
			return array(
				'status'      => 'unavailable',
				'label'       => __( 'Source health unavailable', 'acf-schema-guard' ),
				'description' => __( 'ACF Local JSON could not be inspected.', 'acf-schema-guard' ),
				'counts'      => $counts,
			);
		}

		foreach ( $report->findings() as $finding ) {
			if ( is_object( $finding ) && method_exists( $finding, 'status' ) && array_key_exists( $finding->status(), $counts ) ) {
				++$counts[ $finding->status() ];
			}
		}

		$issues = $counts['database_only'] + $counts['json_only'] + $counts['divergent'];

		return array(
			'status'      => $issues > 0 ? 'attention' : 'aligned',
			'label'       => $issues > 0 ? __( 'Source review needed', 'acf-schema-guard' ) : __( 'Sources aligned', 'acf-schema-guard' ),
			'description' => sprintf( _n( '%d field group needs source review.', '%d field groups need source review.', $issues, 'acf-schema-guard' ), $issues ),
			'counts'      => $counts,
		);
	}

	private function available_scanner_roots() {
		$roots = array(); foreach ( wp_get_themes() as $stylesheet => $theme ) { $roots[ 'theme:' . $stylesheet ] = sprintf( __( 'Theme: %s', 'acf-schema-guard' ), $theme->get( 'Name' ) ); }
		if ( ! function_exists( 'get_plugins' ) ) { require_once ABSPATH . 'wp-admin/includes/plugin.php'; }
		foreach ( get_plugins() as $plugin_file => $plugin ) { $directory = dirname( $plugin_file ); if ( '.' !== $directory ) { $roots[ 'plugin:' . $directory ] = sprintf( __( 'Plugin: %s', 'acf-schema-guard' ), $plugin['Name'] ); } }
		return $roots;
	}

	private function scanner_root_path( $identifier ) { return 0 === strpos( $identifier, 'theme:' ) ? WP_CONTENT_DIR . '/themes/' . substr( $identifier, 6 ) : WP_CONTENT_DIR . '/plugins/' . substr( $identifier, 7 ); }

	public function save_scanner_roots() {
		if ( ! current_user_can( $this->capability ) ) { wp_die( esc_html__( 'You do not have permission to update scanner settings.', 'acf-schema-guard' ) ); }
		check_admin_referer( 'acf_schema_guard_save_scanner_roots' );
		$roots = isset( $_POST['scanner_roots'] ) && is_array( $_POST['scanner_roots'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['scanner_roots'] ) ) : array();
		if ( class_exists( '\\AcfSchemaGuard\\Scanner\\ScannerConfiguration' ) ) { ( new \AcfSchemaGuard\Scanner\ScannerConfiguration() )->save( is_array( $roots ) ? $roots : array() ); }
		wp_safe_redirect( admin_url( 'admin.php?page=acf-schema-guard-settings' ) );
		exit;
	}

	private function render_code_usage_page( array $screen ) {
		$references          = $this->scan_current_references();
		$dynamic_references  = $this->scan_current_dynamic_references();
		$filters             = $this->code_usage_filters( $references );
		$filtered            = $this->filter_code_references( $references, $filters );
		$filtered_dynamic    = $this->filter_dynamic_code_references( $dynamic_references, $filters );
		$references_by_field = $this->references_by_field( $filtered );

		?>
		<div class="wrap acf-schema-guard-admin acf-schema-guard-code-usage-page">
			<h1><?php echo esc_html( __( $screen['title'], 'acf-schema-guard' ) ); ?></h1>
			<p><?php echo esc_html__( 'Literal PHP ACF references found in the configured themes and plugins as they exist now. Each field groups all of its real call sites together.', 'acf-schema-guard' ); ?></p>

			<form method="get" class="acf-schema-guard-code-usage-filter">
				<input type="hidden" name="page" value="acf-schema-guard-code-usage" />
				<div class="acf-schema-guard-filter-control"><label for="acf-schema-guard-field"><?php echo esc_html__( 'Field', 'acf-schema-guard' ); ?></label>
				<select id="acf-schema-guard-field" name="acf_schema_guard_field">
					<option value=""><?php echo esc_html__( 'All fields', 'acf-schema-guard' ); ?></option>
					<?php foreach ( $filters['available_fields'] as $field_name ) : ?>
						<option value="<?php echo esc_attr( $field_name ); ?>" <?php selected( $filters['field'], $field_name ); ?>><?php echo esc_html( $field_name ); ?></option>
					<?php endforeach; ?>
				</select></div>

				<div class="acf-schema-guard-filter-control"><label for="acf-schema-guard-file"><?php echo esc_html__( 'File', 'acf-schema-guard' ); ?></label>
				<select id="acf-schema-guard-file" name="acf_schema_guard_file">
					<option value=""><?php echo esc_html__( 'All files', 'acf-schema-guard' ); ?></option>
					<?php foreach ( $filters['available_files'] as $path ) : ?>
						<option value="<?php echo esc_attr( $path ); ?>" <?php selected( $filters['file'], $path ); ?>><?php echo esc_html( $path ); ?></option>
					<?php endforeach; ?>
				</select></div>

				<div class="acf-schema-guard-filter-control"><label for="acf-schema-guard-function"><?php echo esc_html__( 'ACF function', 'acf-schema-guard' ); ?></label>
				<select id="acf-schema-guard-function" name="acf_schema_guard_function">
					<option value=""><?php echo esc_html__( 'All supported functions', 'acf-schema-guard' ); ?></option>
					<?php foreach ( $filters['available_functions'] as $function_name ) : ?>
						<option value="<?php echo esc_attr( $function_name ); ?>" <?php selected( $filters['function'], $function_name ); ?>><?php echo esc_html( $function_name ); ?></option>
					<?php endforeach; ?>
				</select></div>

				<div class="acf-schema-guard-filter-action"><?php submit_button( __( 'Filter references', 'acf-schema-guard' ), 'secondary', 'submit', false ); ?></div>
			</form>

			<p class="acf-schema-guard-code-usage-count">
				<?php echo esc_html( sprintf( _n( '%d reference shown', '%d references shown', count( $filtered ), 'acf-schema-guard' ), count( $filtered ) ) ); ?>
			</p>

			<?php $this->render_dynamic_reference_notice( $filtered_dynamic ); ?>

			<?php if ( empty( $references_by_field ) ) : ?>
				<section class="acf-schema-guard-empty-state acf-schema-guard-code-usage-empty-state">
					<h2><?php echo esc_html__( 'No matching references', 'acf-schema-guard' ); ?></h2>
					<p><?php echo esc_html__( 'Try changing the filters, or choose source roots in Settings before scanning again.', 'acf-schema-guard' ); ?></p>
				</section>
			<?php else : ?>
				<div class="acf-schema-guard-code-usage-list">
					<?php foreach ( $references_by_field as $field_name => $items ) : ?>
						<details class="acf-schema-guard-code-reference">
							<summary>
								<span>
								<code><?php echo esc_html( $field_name ); ?></code>
								<strong><?php echo esc_html( sprintf( _n( '%d reference', '%d references', count( $items ), 'acf-schema-guard' ), count( $items ) ) ); ?></strong>
							</span>
							<span class="acf-schema-guard-code-disclosure-hint"><?php echo esc_html__( 'Show call sites', 'acf-schema-guard' ); ?></span>
						</summary>
							<?php foreach ( $items as $item ) : ?>
								<details class="acf-schema-guard-code-location">
									<summary>
									<strong><?php echo esc_html( $item['path'] ); ?>:<?php echo esc_html( $item['line'] ); ?></strong>
									<code><?php echo esc_html( $item['expression'] ); ?></code>
								</summary>
								<p class="acf-schema-guard-code-preview-hint"><?php echo esc_html__( 'PHP context around this call.', 'acf-schema-guard' ); ?></p>
								<pre class="acf-schema-guard-code-preview" data-language="PHP"><code><?php echo esc_html( $this->code_snippet( isset( $item['root'] ) ? $item['root'] : $source_root, $item['path'], $item['line'] ) ); ?></code></pre>
								</details>
							<?php endforeach; ?>
						</details>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	private function scan_current_references() {
		if (
			! class_exists( '\\AcfSchemaGuard\\Scanner\\CurrentCodeUsageService' )
			|| ! class_exists( '\\AcfSchemaGuard\\Scanner\\ScannerConfiguration' )
			|| ! class_exists( '\\AcfSchemaGuard\\Scanner\\CodeUsageScannerService' )
			|| ! class_exists( '\\AcfSchemaGuard\\Scanner\\PhpAcfUsageScanner' )
		) {
			return array();
		}

		$scanner = new \AcfSchemaGuard\Scanner\CodeUsageScannerService(
			array( new \AcfSchemaGuard\Scanner\PhpAcfUsageScanner() )
		);

		return ( new \AcfSchemaGuard\Scanner\CurrentCodeUsageService(
			$scanner,
			new \AcfSchemaGuard\Scanner\ScannerConfiguration()
		) )->references();
	}

	private function scan_current_dynamic_references() {
		if (
			! class_exists( '\\AcfSchemaGuard\\Scanner\\CurrentCodeUsageService' )
			|| ! class_exists( '\\AcfSchemaGuard\\Scanner\\ScannerConfiguration' )
			|| ! class_exists( '\\AcfSchemaGuard\\Scanner\\CodeUsageScannerService' )
			|| ! class_exists( '\\AcfSchemaGuard\\Scanner\\PhpAcfUsageScanner' )
		) {
			return array();
		}

		$scanner = new \AcfSchemaGuard\Scanner\CodeUsageScannerService(
			array( new \AcfSchemaGuard\Scanner\PhpAcfUsageScanner() )
		);

		return ( new \AcfSchemaGuard\Scanner\CurrentCodeUsageService(
			$scanner,
			new \AcfSchemaGuard\Scanner\ScannerConfiguration()
		) )->dynamic_references();
	}

	private function code_usage_filters( array $references ) {
		$fields    = array();
		$files     = array();
		$functions = array();

		foreach ( $references as $reference ) {
			$item = $reference->to_array();
			$fields[]    = $item['field_name'];
			$files[]     = $item['path'];
			$functions[] = $this->reference_function( $item['expression'] );
		}

		return array(
			'field'               => $this->code_usage_request_value( 'acf_schema_guard_field', $fields ),
			'file'                => $this->code_usage_request_value( 'acf_schema_guard_file', $files ),
			'function'            => $this->code_usage_request_value( 'acf_schema_guard_function', $functions ),
			'available_fields'    => $this->sorted_unique_values( $fields ),
			'available_files'     => $this->sorted_unique_values( $files ),
			'available_functions' => $this->sorted_unique_values( $functions ),
		);
	}

	private function code_usage_request_value( $key, array $available_values ) {
		$value = isset( $_GET[ $key ] ) ? sanitize_text_field( wp_unslash( $_GET[ $key ] ) ) : '';

		return in_array( $value, $available_values, true ) ? $value : '';
	}

	private function sorted_unique_values( array $values ) {
		$values = array_unique( array_filter( $values ) );
		sort( $values, SORT_STRING );

		return array_values( $values );
	}

	private function filter_code_references( array $references, array $filters ) {
		$filtered = array();

		foreach ( $references as $reference ) {
			$item = $reference->to_array();
			if ( '' !== $filters['field'] && $filters['field'] !== $item['field_name'] ) {
				continue;
			}
			if ( '' !== $filters['file'] && $filters['file'] !== $item['path'] ) {
				continue;
			}
			if ( '' !== $filters['function'] && $filters['function'] !== $this->reference_function( $item['expression'] ) ) {
				continue;
			}
			$filtered[] = $item;
		}

		return $filtered;
	}

	private function filter_dynamic_code_references( array $references, array $filters ) {
		$filtered = array();

		foreach ( $references as $reference ) {
			$item = $reference->to_array();
			if ( '' !== $filters['file'] && $filters['file'] !== $item['path'] ) {
				continue;
			}
			if ( '' !== $filters['function'] && $filters['function'] !== $this->reference_function( $item['expression'] ) ) {
				continue;
			}
			$filtered[] = $item;
		}

		return $filtered;
	}

	private function render_dynamic_reference_notice( array $references ) {
		if ( empty( $references ) ) {
			return;
		}
		?>
		<section class="acf-schema-guard-dynamic-reference-notice">
			<h2><?php echo esc_html__( 'Dynamic ACF calls need manual review', 'acf-schema-guard' ); ?></h2>
			<p><?php echo esc_html__( 'These supported calls use a non-literal field argument. ACF Schema Guard cannot safely determine which field they access, so they are not linked to a specific schema change.', 'acf-schema-guard' ); ?></p>
			<ul>
				<?php foreach ( $references as $reference ) : ?>
					<li><code><?php echo esc_html( $reference['path'] ); ?>:<?php echo esc_html( $reference['line'] ); ?></code><code><?php echo esc_html( $reference['expression'] ); ?></code><span><?php echo esc_html__( 'Manual review required', 'acf-schema-guard' ); ?></span></li>
				<?php endforeach; ?>
			</ul>
		</section>
		<?php
	}

	private function references_by_field( array $references ) {
		$grouped = array();

		foreach ( $references as $reference ) {
			$grouped[ $reference['field_name'] ][] = $reference;
		}

		ksort( $grouped, SORT_STRING );

		return $grouped;
	}

	private function reference_function( $expression ) {
		if ( preg_match( '/^([a-z_]+)\\s*\\(/i', $expression, $matches ) ) {
			return $matches[1];
		}

		return __( 'Unknown', 'acf-schema-guard' );
	}

	private function code_snippet( $root, $relative_path, $line ) {
		$root_path = realpath( $root );
		$file_path = realpath( trailingslashit( $root ) . ltrim( $relative_path, '/' ) );

		if (
			false === $root_path
			|| false === $file_path
			|| 0 !== strpos( $file_path, $root_path . DIRECTORY_SEPARATOR )
			|| ! is_readable( $file_path )
		) {
			return __( 'Source preview is unavailable.', 'acf-schema-guard' );
		}

		$lines = file( $file_path );
		if ( false === $lines ) {
			return __( 'Source preview is unavailable.', 'acf-schema-guard' );
		}

		$start  = max( 0, (int) $line - 4 );
		$end    = min( count( $lines ), (int) $line + 3 );
		$output = array();

		for ( $index = $start; $index < $end; $index++ ) {
			$output[] = sprintf( '%4d  %s', $index + 1, rtrim( $lines[ $index ] ) );
		}

		return implode( "\n", $output );
	}

	/**
	 * Renders the read-only ACF Local JSON source-health result.
	 *
	 * @param array<string, string> $screen Screen definition.
	 * @return void
	 */
	private function render_source_health_page( array $screen ) {
		$report = call_user_func( $this->source_health_callback );
		?>
		<div class="wrap acf-schema-guard-admin acf-schema-guard-source-health-page">
			<h1><?php echo esc_html( __( $screen['title'], 'acf-schema-guard' ) ); ?></h1>
			<p><?php echo esc_html__( 'Checks whether each ACF field group is represented consistently in the WordPress database and ACF Local JSON.', 'acf-schema-guard' ); ?></p>
			<?php if ( ! $report->is_available() ) : ?>
				<section class="acf-schema-guard-empty-state">
					<h2><?php echo esc_html__( 'Source health unavailable', 'acf-schema-guard' ); ?></h2>
					<p><?php echo esc_html__( 'ACF is unavailable, so Source health cannot inspect field groups.', 'acf-schema-guard' ); ?></p>
				</section>
			<?php elseif ( empty( $report->findings() ) ) : ?>
				<section class="acf-schema-guard-empty-state">
					<h2><?php echo esc_html__( 'No field groups found', 'acf-schema-guard' ); ?></h2>
					<p><?php echo esc_html__( 'No ACF field groups were found in the database or configured Local JSON paths.', 'acf-schema-guard' ); ?></p>
				</section>
			<?php else : ?>
				<table class="widefat striped acf-schema-guard-source-health">
					<thead><tr><th scope="col"><?php echo esc_html__( 'Field group', 'acf-schema-guard' ); ?></th><th scope="col"><?php echo esc_html__( 'Key', 'acf-schema-guard' ); ?></th><th scope="col"><?php echo esc_html__( 'Status', 'acf-schema-guard' ); ?></th><th scope="col"><?php echo esc_html__( 'Database', 'acf-schema-guard' ); ?></th><th scope="col"><?php echo esc_html__( 'Local JSON', 'acf-schema-guard' ); ?></th><th scope="col"><?php echo esc_html__( 'Recommended action', 'acf-schema-guard' ); ?></th></tr></thead>
					<tbody><?php foreach ( $report->findings() as $finding ) : ?><?php $database = $this->source_health_presence( $finding->database_group(), $finding->status(), $finding->direction(), 'database' ); ?><?php $json = $this->source_health_presence( $finding->json_group(), $finding->status(), $finding->direction(), 'json' ); ?><tr class="acf-schema-guard-source-status-<?php echo esc_attr( $this->source_health_status( $finding->status() ) ); ?>"><td data-label="<?php echo esc_attr__( 'Field group', 'acf-schema-guard' ); ?>"><?php echo esc_html( $finding->title() ); ?></td><td data-label="<?php echo esc_attr__( 'Key', 'acf-schema-guard' ); ?>"><code><?php echo esc_html( $finding->field_group_key() ); ?></code></td><td data-label="<?php echo esc_attr__( 'Status', 'acf-schema-guard' ); ?>"><span class="acf-schema-guard-source-status-label"><?php echo esc_html( $this->source_health_label( $finding->status() ) ); ?></span></td><td data-label="<?php echo esc_attr__( 'Database', 'acf-schema-guard' ); ?>"><span class="acf-schema-guard-source-presence acf-schema-guard-source-presence--<?php echo esc_attr( $database['state'] ); ?>"><strong><?php echo esc_html( $database['label'] ); ?></strong><small><?php echo esc_html( $database['detail'] ); ?></small></span></td><td data-label="<?php echo esc_attr__( 'Local JSON', 'acf-schema-guard' ); ?>"><span class="acf-schema-guard-source-presence acf-schema-guard-source-presence--<?php echo esc_attr( $json['state'] ); ?>"><strong><?php echo esc_html( $json['label'] ); ?></strong><small><?php echo esc_html( $json['detail'] ); ?></small></span></td><td data-label="<?php echo esc_attr__( 'Recommended action', 'acf-schema-guard' ); ?>"><?php echo esc_html( $this->source_health_action( $finding->status(), $finding->direction() ) ); ?></td></tr><?php endforeach; ?></tbody>
				</table>
			<?php endif; ?>
		</div>
		<?php
	}

	private function source_health_status( $status ) {
		return in_array( $status, array( 'aligned', 'database_only', 'json_only', 'divergent' ), true ) ? $status : 'unknown';
	}

	private function source_health_label( $status ) {
		$labels = array( 'aligned' => __( 'Aligned', 'acf-schema-guard' ), 'database_only' => __( 'Database only', 'acf-schema-guard' ), 'json_only' => __( 'Local JSON only', 'acf-schema-guard' ), 'divergent' => __( 'Divergent', 'acf-schema-guard' ) );
		$status = $this->source_health_status( $status );
		return isset( $labels[ $status ] ) ? $labels[ $status ] : __( 'Unknown', 'acf-schema-guard' );
	}

	private function source_health_presence( $source, $status, $direction, $type ) {
		if ( null === $source ) {
			return array( 'state' => 'missing', 'label' => __( 'Missing', 'acf-schema-guard' ), 'detail' => __( 'Not found', 'acf-schema-guard' ) );
		}

		if ( 'aligned' === $this->source_health_status( $status ) ) {
			return array( 'state' => 'aligned', 'label' => __( 'Present', 'acf-schema-guard' ), 'detail' => __( 'Matches', 'acf-schema-guard' ) );
		}

		if ( 'divergent' === $status && in_array( $direction, array( 'equal', 'unknown' ), true ) ) {
			return array( 'state' => 'conflict', 'label' => __( 'Present', 'acf-schema-guard' ), 'detail' => __( 'Same modified time', 'acf-schema-guard' ) );
		}

		if ( ( 'database' === $type && 'database_newer' === $direction ) || ( 'json' === $type && 'json_newer' === $direction ) ) {
			return array( 'state' => 'newer', 'label' => __( 'Present', 'acf-schema-guard' ), 'detail' => __( 'Newer schema', 'acf-schema-guard' ) );
		}

		return array( 'state' => 'present', 'label' => __( 'Present', 'acf-schema-guard' ), 'detail' => __( 'Review', 'acf-schema-guard' ) );
	}

	private function source_health_action( $status, $direction = 'unknown' ) {
		$status = $this->source_health_status( $status );

		if ( 'aligned' === $status ) {
			return __( 'No action needed.', 'acf-schema-guard' );
		}

		if ( 'divergent' === $status && in_array( $direction, array( 'equal', 'unknown' ), true ) ) {
			return __( 'Definitions differ but timestamps are equal. ACF Sync may not be available.', 'acf-schema-guard' );
		}

		if ( 'json_newer' === $direction ) { return __( 'Local JSON is newer. Review it, then use ACF Sync to import it into the database.', 'acf-schema-guard' ); }
		if ( 'database_newer' === $direction ) { return __( 'The database is newer. Open this group in ACF and save it to refresh Local JSON before committing.', 'acf-schema-guard' ); }
		$actions = array( 'aligned' => __( 'No action needed.', 'acf-schema-guard' ), 'database_only' => __( 'Save or sync this group so it is written to Local JSON and committed to Git.', 'acf-schema-guard' ), 'json_only' => __( 'Review the JSON definition and import or sync it into the database when appropriate.', 'acf-schema-guard' ), 'divergent' => __( 'Review both definitions before synchronizing. Do not overwrite either source blindly.', 'acf-schema-guard' ) );
		return isset( $actions[ $status ] ) ? $actions[ $status ] : __( 'Review this field group manually.', 'acf-schema-guard' );
	}

	/**
	 * Gets the screen definition for the requested page slug.
	 *
	 * @return array<string, string>|null
	 */
	private function current_screen( $page ) {
		return isset( self::$screens[ $page ] ) ? self::$screens[ $page ] : null;
	}

	/**
	 * Gets the requested plugin page slug.
	 *
	 * @return string
	 */
	private function current_page() {
		return isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';
	}

	/**
	 * Renders the stored snapshot history.
	 *
	 * @param array<string, string> $screen Screen definition.
	 * @return void
	 */
	private function render_history_page( array $screen ) {
		$snapshots = $this->snapshots->recent( self::HISTORY_SNAPSHOT_LIMIT );
		$baseline = $this->baseline->snapshot();
		?>
		<div class="wrap acf-schema-guard-admin acf-schema-guard-history-page">
			<h1><?php echo esc_html( __( $screen['title'], 'acf-schema-guard' ) ); ?></h1>
			<p><?php echo esc_html( __( 'Stored immutable schema snapshots, newest first.', 'acf-schema-guard' ) ); ?></p>
			<?php $this->render_history_notice(); ?>
			<section class="acf-schema-guard-history-action">
				<div>
					<p class="acf-schema-guard-overview-eyebrow"><?php echo esc_html__( 'Current schema', 'acf-schema-guard' ); ?></p>
					<h2><?php echo esc_html__( 'Capture a checkpoint', 'acf-schema-guard' ); ?></h2>
					<p><?php echo esc_html__( 'Save the effective ACF schema as an immutable snapshot before or after a meaningful change.', 'acf-schema-guard' ); ?></p>
				</div>
				<form class="acf-schema-guard-capture-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="acf_schema_guard_capture_snapshot" />
					<?php wp_nonce_field( 'acf_schema_guard_capture_snapshot' ); ?>
					<?php submit_button( __( 'Capture current schema', 'acf-schema-guard' ), 'primary', 'submit', false ); ?>
				</form>
			</section>
			<?php if ( empty( $snapshots ) ) : ?>
				<section class="acf-schema-guard-empty-state">
					<h2><?php echo esc_html__( 'No snapshots yet', 'acf-schema-guard' ); ?></h2>
					<p><?php echo esc_html__( 'Capture the current schema to start a durable history and choose a baseline for live comparison.', 'acf-schema-guard' ); ?></p>
				</section>
			<?php else : ?>
				<table class="widefat striped acf-schema-guard-snapshots">
					<thead>
						<tr>
							<th scope="col"><?php echo esc_html__( 'Snapshot ID', 'acf-schema-guard' ); ?></th>
							<th scope="col"><?php echo esc_html__( 'Source', 'acf-schema-guard' ); ?></th>
							<th scope="col"><?php echo esc_html__( 'Captured (UTC)', 'acf-schema-guard' ); ?></th>
							<th scope="col"><?php echo esc_html__( 'Baseline', 'acf-schema-guard' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $snapshots as $snapshot ) : ?>
							<tr>
								<td data-label="<?php echo esc_attr__( 'Snapshot ID', 'acf-schema-guard' ); ?>"><code><?php echo esc_html( $snapshot->id() ); ?></code></td>
								<td data-label="<?php echo esc_attr__( 'Source', 'acf-schema-guard' ); ?>"><?php echo esc_html( 'acf-auto' === $snapshot->source_id() ? __( 'Automatic ACF save', 'acf-schema-guard' ) : $snapshot->source_id() ); ?></td>
								<td data-label="<?php echo esc_attr__( 'Captured (UTC)', 'acf-schema-guard' ); ?>"><?php echo esc_html( $snapshot->created_at() ); ?></td>
								<td data-label="<?php echo esc_attr__( 'Baseline', 'acf-schema-guard' ); ?>"><?php if ( $baseline && $baseline->id() === $snapshot->id() ) { echo esc_html__( 'Approved baseline', 'acf-schema-guard' ); } else { ?><form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"><input type="hidden" name="action" value="acf_schema_guard_set_baseline_snapshot" /><input type="hidden" name="snapshot_id" value="<?php echo esc_attr( $snapshot->id() ); ?>" /><?php wp_nonce_field( 'acf_schema_guard_set_baseline_snapshot' ); submit_button( __( 'Set as baseline', 'acf-schema-guard' ), 'secondary small', 'submit', false ); ?></form><?php } ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		</div>
		<?php
	}

	public function set_baseline_snapshot() {
		if ( ! current_user_can( $this->capability ) ) { wp_die( esc_html__( 'You do not have permission to set a baseline.', 'acf-schema-guard' ) ); }
		check_admin_referer( 'acf_schema_guard_set_baseline_snapshot' );
		$id = isset( $_POST['snapshot_id'] ) ? sanitize_text_field( wp_unslash( $_POST['snapshot_id'] ) ) : '';
		$snapshot = $this->snapshots->find( $id );
		if ( null !== $snapshot ) { $this->baseline->set( $snapshot ); }
		wp_safe_redirect( admin_url( 'admin.php?page=acf-schema-guard-history' ) ); exit;
	}

	/**
	 * Renders the snapshot-selection form for schema comparisons.
	 *
	 * @param array<string, string> $screen Screen definition.
	 * @return void
	 */
	private function render_changes_page( array $screen ) {
		if ( ! is_callable( $this->analyze_live_baseline_callback ) ) {
			$this->render_changes_state( $screen, __( 'Live schema comparison is unavailable.', 'acf-schema-guard' ) );
			return;
		}

		$live = call_user_func( $this->analyze_live_baseline_callback );
		if ( ! is_object( $live ) || ! method_exists( $live, 'is_available' ) || ! $live->is_available() ) {
			$message = is_object( $live ) && method_exists( $live, 'message' ) ? $live->message() : __( 'The current ACF schema could not be compared.', 'acf-schema-guard' );
			$this->render_changes_state( $screen, $message );
			return;
		}

		$baseline = $live->baseline();
		$analysis = $live->analysis();
		?>
		<div class="wrap acf-schema-guard-admin acf-schema-guard-changes-page">
			<h1><?php echo esc_html( __( $screen['title'], 'acf-schema-guard' ) ); ?></h1>
			<p><?php echo esc_html__( 'Comparing the approved baseline with the current live schema.', 'acf-schema-guard' ); ?></p>
			<div class="acf-schema-guard-changes-context">
				<div>
					<p class="acf-schema-guard-overview-eyebrow"><?php echo esc_html__( 'Approved baseline', 'acf-schema-guard' ); ?></p>
					<strong><?php echo esc_html( $baseline->created_at() ); ?></strong>
					<code><?php echo esc_html( $baseline->id() ); ?></code>
				</div>
				<div>
					<p class="acf-schema-guard-overview-eyebrow"><?php echo esc_html__( 'Current schema', 'acf-schema-guard' ); ?></p>
					<strong><?php echo esc_html__( 'Live schema', 'acf-schema-guard' ); ?></strong>
					<span><?php echo esc_html__( 'Loaded for this request and not saved as a snapshot.', 'acf-schema-guard' ); ?></span>
				</div>
			</div>
			<?php $this->render_comparison_results( $analysis ); ?>
		</div>
		<?php
	}

	private function render_changes_state( array $screen, $message ) {
		?>
		<div class="wrap acf-schema-guard-admin acf-schema-guard-changes-page"><h1><?php echo esc_html( __( $screen['title'], 'acf-schema-guard' ) ); ?></h1><div class="notice notice-info inline"><p><?php echo esc_html( $message ); ?></p></div></div>
		<?php
	}

	/**
	 * Renders classified findings for one validated schema analysis.
	 *
	 * @param \AcfSchemaGuard\Diff\SnapshotAnalysis $analysis Classified analysis.
	 * @return void
	 */
	private function render_comparison_results( $analysis, $after_snapshot = null ) {
		try {
			if ( null === $analysis || null !== $after_snapshot ) {
				$analysis = call_user_func( $this->analyze_snapshots_callback, $analysis, $after_snapshot );
			}

			$analysis = $analysis->to_array();
		} catch ( \RuntimeException $exception ) {
			?>
			<div class="notice notice-error inline"><p><?php echo esc_html__( 'The selected snapshots could not be compared. Try again.', 'acf-schema-guard' ); ?></p></div>
			<?php

			return;
		}

		if ( empty( $analysis['findings'] ) ) {
			?>
			<div class="notice notice-success inline"><p><?php echo esc_html__( 'No schema changes found between the selected snapshots.', 'acf-schema-guard' ); ?></p></div>
			<?php

			return;
		}

		$impacts_by_change = $this->code_impacts_by_change( $analysis['findings'] );
		$data_impacts_by_change = $this->stored_data_impacts_by_change( $analysis['findings'] );
		$dynamic_references = $this->scan_current_dynamic_references();

		?>
		<div class="acf-schema-guard-changes-results-heading">
			<h2><?php echo esc_html__( 'Detected changes', 'acf-schema-guard' ); ?></h2>
			<p><?php echo esc_html( sprintf( __( '%d classified changes need review.', 'acf-schema-guard' ), count( $analysis['findings'] ) ) ); ?></p>
		</div>
		<?php
		$this->render_severity_legend();
		$this->render_dynamic_reference_notice( array_map( static function ( $reference ) { return $reference->to_array(); }, $dynamic_references ) );
		?>
		<table class="widefat striped acf-schema-guard-findings">
			<thead><tr>
				<th scope="col"><?php echo esc_html__( 'Kind', 'acf-schema-guard' ); ?></th>
				<th scope="col"><?php echo esc_html__( 'Node type', 'acf-schema-guard' ); ?></th>
				<th scope="col"><?php echo esc_html__( 'Path', 'acf-schema-guard' ); ?></th>
				<th scope="col"><?php echo esc_html__( 'Change details', 'acf-schema-guard' ); ?></th>
				<th scope="col"><?php echo esc_html__( 'Severity', 'acf-schema-guard' ); ?></th>
				<th scope="col"><?php echo esc_html__( 'Rationale', 'acf-schema-guard' ); ?></th>
			</tr></thead>
			<tbody>
				<?php foreach ( $analysis['findings'] as $finding ) : ?>
					<?php
					$change   = $finding['change'];
					$severity = $this->normalize_severity( isset( $finding['severity'] ) ? $finding['severity'] : '' );
					$impacts  = isset( $impacts_by_change[ $this->change_key( $change ) ] ) ? $impacts_by_change[ $this->change_key( $change ) ] : array();
					$data_impacts = isset( $data_impacts_by_change[ $this->change_key( $change ) ] ) ? $data_impacts_by_change[ $this->change_key( $change ) ] : array();
					?>
					<tr class="acf-schema-guard-finding acf-schema-guard-finding-<?php echo esc_attr( $severity ); ?>">
						<td data-label="<?php echo esc_attr__( 'Kind', 'acf-schema-guard' ); ?>"><?php echo esc_html( $change['kind'] ); ?></td>
						<td data-label="<?php echo esc_attr__( 'Node type', 'acf-schema-guard' ); ?>"><?php echo esc_html( $change['node_type'] ); ?></td>
						<td data-label="<?php echo esc_attr__( 'Path', 'acf-schema-guard' ); ?>"><code><?php echo esc_html( implode( '.', $change['path'] ) ); ?></code></td>
						<td data-label="<?php echo esc_attr__( 'Change details', 'acf-schema-guard' ); ?>"><?php $this->render_change_explanation( isset( $finding['explanation'] ) ? $finding['explanation'] : array() ); ?></td>
						<td data-label="<?php echo esc_attr__( 'Severity', 'acf-schema-guard' ); ?>"><?php $this->render_severity_badge( $severity ); ?></td>
						<td data-label="<?php echo esc_attr__( 'Rationale', 'acf-schema-guard' ); ?>"><?php echo esc_html( $finding['rationale'] ); ?></td>
					</tr>
					<?php if ( ! empty( $impacts ) || ! empty( $data_impacts ) ) : ?>
						<tr class="acf-schema-guard-code-impacts">
							<td colspan="6"><?php if ( ! empty( $impacts ) ) { $this->render_code_impacts( $impacts ); } if ( ! empty( $data_impacts ) ) { $this->render_stored_data_impacts( $data_impacts ); } ?></td>
						</tr>
					<?php endif; ?>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php
	}

	private function code_impacts_by_change( array $findings ) {
		$changes = array();

		foreach ( $findings as $finding ) {
			if ( isset( $finding['change'] ) && is_array( $finding['change'] ) ) {
				$changes[] = $finding['change'];
			}
		}

		if ( empty( $changes ) ) {
			return array();
		}
		if ( ! is_callable( $this->analyze_code_impact_callback ) ) {
			return array();
		}

		$references  = $this->scan_current_references();
		$impacts     = call_user_func( $this->analyze_code_impact_callback, $changes, $references );
		$grouped     = array();

		foreach ( $impacts as $impact ) {
			$data = is_object( $impact ) && method_exists( $impact, 'to_array' ) ? $impact->to_array() : array();
			if ( ! isset( $data['change'], $data['reference'] ) || ! is_array( $data['change'] ) || ! is_array( $data['reference'] ) ) {
				continue;
			}
			$grouped[ $this->change_key( $data['change'] ) ][] = $data;
		}

		return $grouped;
	}

	private function stored_data_impacts_by_change( array $findings ) {
		$changes = array();

		foreach ( $findings as $finding ) {
			if ( isset( $finding['change'] ) && is_array( $finding['change'] ) ) {
				$changes[] = $finding['change'];
			}
		}

		if ( empty( $changes ) || ! is_callable( $this->analyze_stored_data_impact_callback ) ) {
			return array();
		}

		$grouped = array();
		foreach ( call_user_func( $this->analyze_stored_data_impact_callback, $changes ) as $impact ) {
			$data = is_object( $impact ) && method_exists( $impact, 'to_array' ) ? $impact->to_array() : array();
			if ( ! isset( $data['change'], $data['field_name'], $data['record_count'], $data['records'] ) || ! is_array( $data['change'] ) || ! is_array( $data['records'] ) ) {
				continue;
			}
			$grouped[ $this->change_key( $data['change'] ) ][] = $data;
		}

		return $grouped;
	}

	private function change_key( array $change ) {
		return implode(
			'|',
			array(
				isset( $change['kind'] ) ? $change['kind'] : '',
				isset( $change['node_type'] ) ? $change['node_type'] : '',
				isset( $change['path'] ) && is_array( $change['path'] ) ? implode( '.', $change['path'] ) : '',
				isset( $change['before']['name'] ) ? $change['before']['name'] : '',
			)
		);
	}

	private function render_code_impacts( array $impacts ) {
		?>
		<section class="acf-schema-guard-code-impact-list">
			<h3><?php echo esc_html__( 'Affected code references', 'acf-schema-guard' ); ?></h3>
			<p><?php echo esc_html__( 'These literal PHP ACF calls still use the field before this schema change.', 'acf-schema-guard' ); ?></p>
			<ul>
				<?php foreach ( $impacts as $impact ) : ?>
					<li>
						<?php $this->render_severity_badge( $impact['severity'] ); ?>
						<code><?php echo esc_html( $impact['reference']['path'] ); ?>:<?php echo esc_html( $impact['reference']['line'] ); ?></code>
						<code><?php echo esc_html( $impact['reference']['expression'] ); ?></code>
					</li>
				<?php endforeach; ?>
			</ul>
		</section>
		<?php
	}

	private function render_stored_data_impacts( array $impacts ) {
		?>
		<section class="acf-schema-guard-stored-data-impact-list">
			<h3><?php echo esc_html__( 'Stored data impact', 'acf-schema-guard' ); ?></h3>
			<p><?php echo esc_html__( 'Evidence identifies records to review. Values are never read or shown, and a zero result does not confirm that no data exists elsewhere.', 'acf-schema-guard' ); ?></p>
			<?php foreach ( $impacts as $impact ) : ?>
					<?php $presentation = $this->stored_data_impact_presentation( $impact ); ?>
					<div class="acf-schema-guard-stored-data-impact-field acf-schema-guard-stored-data-impact-field--<?php echo esc_attr( $presentation['kind'] ); ?>">
						<div class="acf-schema-guard-stored-data-impact-heading">
							<strong><code><?php echo esc_html( $impact['field_name'] ); ?></code></strong>
							<span class="acf-schema-guard-storage-evidence acf-schema-guard-storage-evidence--<?php echo esc_attr( $presentation['kind'] ); ?>"><?php echo esc_html( $presentation['label'] ); ?></span>
						</div>
						<p><?php echo esc_html( $presentation['description'] ); ?></p>
						<?php if ( ! empty( $presentation['path'] ) || ! empty( $presentation['pattern'] ) ) : ?>
							<dl class="acf-schema-guard-storage-details">
								<?php if ( ! empty( $presentation['path'] ) ) : ?><div><dt><?php echo esc_html__( 'Field path', 'acf-schema-guard' ); ?></dt><dd><code><?php echo esc_html( $presentation['path'] ); ?></code></dd></div><?php endif; ?>
								<?php if ( ! empty( $presentation['pattern'] ) ) : ?><div><dt><?php echo esc_html__( 'Storage pattern', 'acf-schema-guard' ); ?></dt><dd><code><?php echo esc_html( $presentation['pattern'] ); ?></code></dd></div><?php endif; ?>
							</dl>
						<?php endif; ?>
						<?php if ( 'unknown' === $presentation['kind'] ) : ?>
							<p class="acf-schema-guard-storage-empty"><?php echo esc_html__( 'No storage query was run for this structure.', 'acf-schema-guard' ); ?></p>
						<?php elseif ( empty( $impact['records'] ) ) : ?>
							<p class="acf-schema-guard-storage-empty"><?php echo esc_html( $presentation['empty_message'] ); ?></p>
						<?php else : ?>
							<span class="acf-schema-guard-storage-count"><?php echo esc_html( sprintf( _n( '%d matching record found', '%d matching records found', $impact['record_count'], 'acf-schema-guard' ), $impact['record_count'] ) ); ?></span>
							<ul>
							<?php foreach ( $impact['records'] as $record ) : ?>
								<li><code>#<?php echo esc_html( $record['post_id'] ); ?></code><span><?php echo esc_html( $record['post_type'] ); ?> · <?php echo esc_html( $record['post_status'] ); ?></span><strong><?php echo esc_html( $record['post_title'] ); ?></strong></li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</section>
		<?php
	}

	private function stored_data_impact_presentation( array $impact ) {
		$matcher    = isset( $impact['matcher'] ) && is_array( $impact['matcher'] ) ? $impact['matcher'] : array();
		$confidence = isset( $matcher['confidence'] ) ? (string) $matcher['confidence'] : 'direct';
		$field_name = isset( $impact['field_name'] ) ? (string) $impact['field_name'] : '';

		if ( 'nested' === $confidence ) {
			$path = isset( $matcher['field_path'] ) && is_array( $matcher['field_path'] ) ? array_filter( array_map( 'strval', $matcher['field_path'] ) ) : array();

			return array(
				'kind'          => 'nested',
				'label'         => __( 'Nested ACF storage', 'acf-schema-guard' ),
				'description'   => __( 'Records match a storage key derived from this field’s ACF parent structure.', 'acf-schema-guard' ),
				'path'          => implode( ' → ', $path ),
				'pattern'       => isset( $matcher['pattern'] ) ? (string) $matcher['pattern'] : '',
				'empty_message' => __( 'No matching nested records were found. Other unsupported storage locations are not included.', 'acf-schema-guard' ),
			);
		}

		if ( 'unknown' === $confidence ) {
			return array(
				'kind'          => 'unknown',
				'label'         => __( 'Coverage unknown', 'acf-schema-guard' ),
				'description'   => __( 'This ACF structure does not have a storage pattern the plugin can verify safely.', 'acf-schema-guard' ),
				'path'          => '',
				'pattern'       => '',
				'empty_message' => '',
			);
		}

		return array(
			'kind'          => 'direct',
			'label'         => __( 'Direct storage', 'acf-schema-guard' ),
			'description'   => __( 'Records use the exact previous field name as their post-meta key.', 'acf-schema-guard' ),
			'path'          => '',
			'pattern'       => $field_name,
			'empty_message' => __( 'No direct records were found. Nested fields and other storage locations are not included.', 'acf-schema-guard' ),
		);
	}

	/**
	 * Renders the visible severity key used by the findings table.
	 *
	 * @return void
	 */
	private function render_severity_legend() {
		?>
		<section class="acf-schema-guard-severity-legend" aria-labelledby="acf-schema-guard-severity-legend-title">
			<h2 id="acf-schema-guard-severity-legend-title"><?php echo esc_html__( 'Severity guide', 'acf-schema-guard' ); ?></h2>
			<ul>
				<?php foreach ( $this->severity_definitions() as $severity => $definition ) : ?>
					<li>
						<?php $this->render_severity_badge( $severity ); ?>
						<span><?php echo esc_html( $definition['description'] ); ?></span>
					</li>
				<?php endforeach; ?>
			</ul>
		</section>
		<?php
	}

	/**
	 * Renders one allow-listed severity badge.
	 *
	 * @param string $severity Finding severity.
	 * @return void
	 */
	private function render_severity_badge( $severity ) {
		$severity   = $this->normalize_severity( $severity );
		$definition = $this->severity_definitions()[ $severity ];
		?>
		<span class="acf-schema-guard-severity acf-schema-guard-severity-<?php echo esc_attr( $severity ); ?>"><?php echo esc_html( $definition['label'] ); ?></span>
		<?php
	}

	/**
	 * Returns labels and explanations for supported risk levels.
	 *
	 * @return array<string, array<string, string>>
	 */
	private function severity_definitions() {
		return array(
			'safe'     => array(
				'label'       => __( 'Safe', 'acf-schema-guard' ),
				'description' => __( 'No breaking impact is expected.', 'acf-schema-guard' ),
			),
			'warning'  => array(
				'label'       => __( 'Warning', 'acf-schema-guard' ),
				'description' => __( 'Review the change before deployment.', 'acf-schema-guard' ),
			),
			'high'     => array(
				'label'       => __( 'High', 'acf-schema-guard' ),
				'description' => __( 'The change is likely to break existing usage.', 'acf-schema-guard' ),
			),
			'critical' => array(
				'label'       => __( 'Critical', 'acf-schema-guard' ),
				'description' => __( 'Resolve the breaking change before deployment.', 'acf-schema-guard' ),
			),
		);
	}

	/**
	 * Keeps severity-derived CSS classes within the supported set.
	 *
	 * @param mixed $severity Finding severity.
	 * @return string
	 */
	private function normalize_severity( $severity ) {
		$severity = is_string( $severity ) ? strtolower( $severity ) : '';

		return isset( $this->severity_definitions()[ $severity ] ) ? $severity : 'warning';
	}

	/**
	 * Renders one shared schema-change explanation.
	 *
	 * @param mixed $explanation Explanation data from snapshot analysis.
	 * @return void
	 */
	private function render_change_explanation( $explanation ) {
		$explanation = is_array( $explanation ) ? $explanation : array();
		$summary     = isset( $explanation['summary'] ) && is_scalar( $explanation['summary'] ) ? (string) $explanation['summary'] : __( 'Schema change.', 'acf-schema-guard' );
		$details = array();

		if ( isset( $explanation['details'] ) && is_array( $explanation['details'] ) ) {
			foreach ( $explanation['details'] as $detail ) {
				if ( is_scalar( $detail ) && '' !== (string) $detail ) {
					$details[] = (string) $detail;
				}
			}
		}
		?>
		<div class="acf-schema-guard-change-explanation">
			<strong class="acf-schema-guard-change-summary"><?php echo esc_html( $summary ); ?></strong>
			<?php if ( ! empty( $details ) ) : ?>
				<ul class="acf-schema-guard-change-details">
					<?php foreach ( $details as $detail ) : ?>
						<li><?php echo esc_html( $detail ); ?></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Captures the current schema through the protected Admin action.
	 *
	 * @return void
	 */
	public function capture_snapshot() {
		if ( ! current_user_can( $this->capability ) ) {
			wp_die( esc_html__( 'You do not have permission to capture a schema snapshot.', 'acf-schema-guard' ) );
		}

		check_admin_referer( 'acf_schema_guard_capture_snapshot' );

		$notice = 'capture-failed';

		try {
			call_user_func( $this->capture_snapshot_callback, 'admin-manual' );
			$notice = 'capture-success';
		} catch ( \RuntimeException $exception ) {
			$notice = 'capture-failed';
		}

		$this->redirect_to_history( $notice );
	}

	/**
	 * Renders the whitelisted capture-result notice.
	 *
	 * @return void
	 */
	private function render_history_notice() {
		$notice = isset( $_GET['acf_schema_guard_notice'] ) ? sanitize_key( wp_unslash( $_GET['acf_schema_guard_notice'] ) ) : '';

		if ( 'capture-success' === $notice ) {
			?>
			<div class="notice notice-success is-dismissible"><p><?php echo esc_html__( 'Schema snapshot captured.', 'acf-schema-guard' ); ?></p></div>
			<?php
		}

		if ( 'capture-failed' === $notice ) {
			?>
			<div class="notice notice-error"><p><?php echo esc_html__( 'The schema snapshot could not be captured. Check that ACF is available and try again.', 'acf-schema-guard' ); ?></p></div>
			<?php
		}
	}

	/**
	 * Redirects to the plugin History screen with a whitelisted notice.
	 *
	 * @param string $notice Capture result notice.
	 * @return void
	 */
	private function redirect_to_history( $notice ) {
		$url = add_query_arg(
			array(
				'page'                    => 'acf-schema-guard-history',
				'acf_schema_guard_notice' => $notice,
			),
			admin_url( 'admin.php' )
		);

		wp_safe_redirect( $url );
		exit;
	}
}
