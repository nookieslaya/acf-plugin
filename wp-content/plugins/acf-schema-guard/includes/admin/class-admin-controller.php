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

	private $baseline;

	/**
	 * @param SnapshotRepository $snapshots                  Stored schema snapshots.
	 * @param callable           $capture_snapshot_callback  Creates a schema snapshot.
	 * @param callable           $analyze_snapshots_callback Analyzes two schema snapshots.
	 */
	public function __construct( SnapshotRepository $snapshots, $capture_snapshot_callback, $analyze_snapshots_callback, BaselineSnapshotService $baseline, $source_health_callback ) {
		$this->snapshots                  = $snapshots;
		$this->capture_snapshot_callback  = $capture_snapshot_callback;
		$this->analyze_snapshots_callback = $analyze_snapshots_callback;
		$this->source_health_callback     = $source_health_callback;
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
				<div class="notice notice-warning inline"><p><?php echo esc_html__( 'ACF is unavailable, so Source health cannot inspect field groups.', 'acf-schema-guard' ); ?></p></div>
			<?php elseif ( empty( $report->findings() ) ) : ?>
				<div class="notice notice-info inline"><p><?php echo esc_html__( 'No ACF field groups were found in the database or configured Local JSON paths.', 'acf-schema-guard' ); ?></p></div>
			<?php else : ?>
				<table class="widefat striped acf-schema-guard-source-health">
					<thead><tr><th scope="col"><?php echo esc_html__( 'Field group', 'acf-schema-guard' ); ?></th><th scope="col"><?php echo esc_html__( 'Key', 'acf-schema-guard' ); ?></th><th scope="col"><?php echo esc_html__( 'Status', 'acf-schema-guard' ); ?></th><th scope="col"><?php echo esc_html__( 'Database', 'acf-schema-guard' ); ?></th><th scope="col"><?php echo esc_html__( 'Local JSON', 'acf-schema-guard' ); ?></th><th scope="col"><?php echo esc_html__( 'Recommended action', 'acf-schema-guard' ); ?></th></tr></thead>
					<tbody><?php foreach ( $report->findings() as $finding ) : ?><tr class="acf-schema-guard-source-status-<?php echo esc_attr( $this->source_health_status( $finding->status() ) ); ?>"><td data-label="<?php echo esc_attr__( 'Field group', 'acf-schema-guard' ); ?>"><?php echo esc_html( $finding->title() ); ?></td><td data-label="<?php echo esc_attr__( 'Key', 'acf-schema-guard' ); ?>"><code><?php echo esc_html( $finding->field_group_key() ); ?></code></td><td data-label="<?php echo esc_attr__( 'Status', 'acf-schema-guard' ); ?>"><span class="acf-schema-guard-source-status-label"><?php echo esc_html( $this->source_health_label( $finding->status() ) ); ?></span></td><td data-label="<?php echo esc_attr__( 'Database', 'acf-schema-guard' ); ?>"><?php echo esc_html( null === $finding->database_group() ? __( 'Missing', 'acf-schema-guard' ) : __( 'Present', 'acf-schema-guard' ) ); ?></td><td data-label="<?php echo esc_attr__( 'Local JSON', 'acf-schema-guard' ); ?>"><?php echo esc_html( null === $finding->json_group() ? __( 'Missing', 'acf-schema-guard' ) : __( 'Present', 'acf-schema-guard' ) ); ?></td><td data-label="<?php echo esc_attr__( 'Recommended action', 'acf-schema-guard' ); ?>"><?php echo esc_html( $this->source_health_action( $finding->status(), $finding->direction() ) ); ?></td></tr><?php endforeach; ?></tbody>
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

	private function source_health_action( $status, $direction = 'unknown' ) {
		if ( 'json_newer' === $direction ) { return __( 'Local JSON is newer. Review it, then use ACF Sync to import it into the database.', 'acf-schema-guard' ); }
		if ( 'database_newer' === $direction ) { return __( 'The database is newer. Open this group in ACF and save it to refresh Local JSON before committing.', 'acf-schema-guard' ); }
		$actions = array( 'aligned' => __( 'No action needed.', 'acf-schema-guard' ), 'database_only' => __( 'Save or sync this group so it is written to Local JSON and committed to Git.', 'acf-schema-guard' ), 'json_only' => __( 'Review the JSON definition and import or sync it into the database when appropriate.', 'acf-schema-guard' ), 'divergent' => __( 'Review both definitions before synchronizing. Do not overwrite either source blindly.', 'acf-schema-guard' ) );
		$status  = $this->source_health_status( $status );
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
		<div class="wrap acf-schema-guard-admin">
			<h1><?php echo esc_html( __( $screen['title'], 'acf-schema-guard' ) ); ?></h1>
			<p><?php echo esc_html( __( 'Stored immutable schema snapshots, newest first.', 'acf-schema-guard' ) ); ?></p>
			<?php $this->render_history_notice(); ?>
			<form class="acf-schema-guard-capture-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="acf_schema_guard_capture_snapshot" />
				<?php wp_nonce_field( 'acf_schema_guard_capture_snapshot' ); ?>
				<?php submit_button( __( 'Capture current schema', 'acf-schema-guard' ), 'primary', 'submit', false ); ?>
			</form>
			<?php if ( empty( $snapshots ) ) : ?>
				<div class="notice notice-info inline">
					<p><?php echo esc_html__( 'No schema snapshots have been captured yet.', 'acf-schema-guard' ); ?></p>
				</div>
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
								<td><code><?php echo esc_html( $snapshot->id() ); ?></code></td>
								<td><?php echo esc_html( $snapshot->source_id() ); ?></td>
								<td><?php echo esc_html( $snapshot->created_at() ); ?></td>
								<td><?php if ( $baseline && $baseline->id() === $snapshot->id() ) { echo esc_html__( 'Approved baseline', 'acf-schema-guard' ); } else { ?><form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"><input type="hidden" name="action" value="acf_schema_guard_set_baseline_snapshot" /><input type="hidden" name="snapshot_id" value="<?php echo esc_attr( $snapshot->id() ); ?>" /><?php wp_nonce_field( 'acf_schema_guard_set_baseline_snapshot' ); submit_button( __( 'Set as baseline', 'acf-schema-guard' ), 'secondary small', 'submit', false ); ?></form><?php } ?></td>
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
		$baseline  = $this->baseline->snapshot();
		$current   = $this->snapshots->latest();
		if ( null === $baseline ) {
			$this->render_changes_state( $screen, __( 'Set an approved baseline in History before reviewing changes.', 'acf-schema-guard' ) );
			return;
		}
		if ( null === $current || $baseline->id() === $current->id() ) {
			$this->render_changes_state( $screen, __( 'Capture a newer schema snapshot after making ACF changes.', 'acf-schema-guard' ) );
			return;
		}
		?>
		<div class="wrap acf-schema-guard-admin acf-schema-guard-changes-page">
			<h1><?php echo esc_html( __( $screen['title'], 'acf-schema-guard' ) ); ?></h1>
			<p><?php echo esc_html__( 'Comparing the approved baseline with the newest captured schema.', 'acf-schema-guard' ); ?></p>
			<p><strong><?php echo esc_html__( 'Baseline:', 'acf-schema-guard' ); ?></strong> <code><?php echo esc_html( $baseline->id() ); ?></code> - <?php echo esc_html( $baseline->created_at() ); ?><br />
			<strong><?php echo esc_html__( 'Current:', 'acf-schema-guard' ); ?></strong> <code><?php echo esc_html( $current->id() ); ?></code> - <?php echo esc_html( $current->created_at() ); ?></p>
			<?php $this->render_comparison_results( $baseline, $current ); ?>
		</div>
		<?php
	}

	private function render_changes_state( array $screen, $message ) {
		?>
		<div class="wrap acf-schema-guard-admin acf-schema-guard-changes-page"><h1><?php echo esc_html( __( $screen['title'], 'acf-schema-guard' ) ); ?></h1><div class="notice notice-info inline"><p><?php echo esc_html( $message ); ?></p></div></div>
		<?php
	}

	/**
	 * Renders classified findings for one validated snapshot pair.
	 *
	 * @param \AcfSchemaGuard\Snapshots\SchemaSnapshot $before_snapshot Earlier snapshot.
	 * @param \AcfSchemaGuard\Snapshots\SchemaSnapshot $after_snapshot Later snapshot.
	 * @return void
	 */
	private function render_comparison_results( $before_snapshot, $after_snapshot ) {
		try {
			$analysis = call_user_func( $this->analyze_snapshots_callback, $before_snapshot, $after_snapshot )->to_array();
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

		$this->render_severity_legend();
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
					?>
					<tr class="acf-schema-guard-finding acf-schema-guard-finding-<?php echo esc_attr( $severity ); ?>">
						<td data-label="<?php echo esc_attr__( 'Kind', 'acf-schema-guard' ); ?>"><?php echo esc_html( $change['kind'] ); ?></td>
						<td data-label="<?php echo esc_attr__( 'Node type', 'acf-schema-guard' ); ?>"><?php echo esc_html( $change['node_type'] ); ?></td>
						<td data-label="<?php echo esc_attr__( 'Path', 'acf-schema-guard' ); ?>"><code><?php echo esc_html( implode( '.', $change['path'] ) ); ?></code></td>
						<td data-label="<?php echo esc_attr__( 'Change details', 'acf-schema-guard' ); ?>"><?php $this->render_change_explanation( isset( $finding['explanation'] ) ? $finding['explanation'] : array() ); ?></td>
						<td data-label="<?php echo esc_attr__( 'Severity', 'acf-schema-guard' ); ?>"><?php $this->render_severity_badge( $severity ); ?></td>
						<td data-label="<?php echo esc_attr__( 'Rationale', 'acf-schema-guard' ); ?>"><?php echo esc_html( $finding['rationale'] ); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php
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
