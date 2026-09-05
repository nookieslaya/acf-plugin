<?php
/**
 * Registers the read-only WordPress Admin foundation.
 *
 * @package ACFSchemaGuard
 */

namespace AcfSchemaGuard\Admin;

use AcfSchemaGuard\Snapshots\SnapshotRepository;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class AdminController {
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

	/**
	 * @param SnapshotRepository $snapshots                  Stored schema snapshots.
	 * @param callable           $capture_snapshot_callback  Creates a schema snapshot.
	 * @param callable           $analyze_snapshots_callback Analyzes two schema snapshots.
	 */
	public function __construct( SnapshotRepository $snapshots, $capture_snapshot_callback, $analyze_snapshots_callback ) {
		$this->snapshots                  = $snapshots;
		$this->capture_snapshot_callback  = $capture_snapshot_callback;
		$this->analyze_snapshots_callback = $analyze_snapshots_callback;
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
		$snapshots = $this->snapshots->all();
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
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $snapshots as $snapshot ) : ?>
							<tr>
								<td><code><?php echo esc_html( $snapshot->id() ); ?></code></td>
								<td><?php echo esc_html( $snapshot->source_id() ); ?></td>
								<td><?php echo esc_html( $snapshot->created_at() ); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Renders the snapshot-selection form for schema comparisons.
	 *
	 * @param array<string, string> $screen Screen definition.
	 * @return void
	 */
	private function render_changes_page( array $screen ) {
		$snapshots = $this->snapshots->all();
		$selection = $this->comparison_selection();
		?>
		<div class="wrap acf-schema-guard-admin">
			<h1><?php echo esc_html( __( $screen['title'], 'acf-schema-guard' ) ); ?></h1>
			<p><?php echo esc_html__( 'Select two stored snapshots to review classified schema changes.', 'acf-schema-guard' ); ?></p>
			<?php if ( 2 > count( $snapshots ) ) : ?>
				<div class="notice notice-info inline">
					<p><?php echo esc_html__( 'Capture at least two schema snapshots before running a comparison.', 'acf-schema-guard' ); ?></p>
				</div>
			<?php else : ?>
				<form class="acf-schema-guard-comparison-form" method="get" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>">
					<input type="hidden" name="page" value="acf-schema-guard-changes" />
					<p>
						<label for="acf-schema-guard-before-snapshot"><?php echo esc_html__( 'Before snapshot', 'acf-schema-guard' ); ?></label>
						<select id="acf-schema-guard-before-snapshot" name="before_snapshot">
							<?php $this->render_snapshot_options( $snapshots, $selection['before_id'] ); ?>
						</select>
					</p>
					<p>
						<label for="acf-schema-guard-after-snapshot"><?php echo esc_html__( 'After snapshot', 'acf-schema-guard' ); ?></label>
						<select id="acf-schema-guard-after-snapshot" name="after_snapshot">
							<?php $this->render_snapshot_options( $snapshots, $selection['after_id'] ); ?>
						</select>
					</p>
					<?php submit_button( __( 'Compare snapshots', 'acf-schema-guard' ), 'primary', 'submit', false ); ?>
				</form>
				<?php $this->render_comparison_notice( $selection['notice'] ); ?>
				<?php if ( '' === $selection['notice'] ) : ?>
					<?php $this->render_comparison_results( $selection['before_snapshot'], $selection['after_snapshot'] ); ?>
				<?php endif; ?>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Gets and validates the requested snapshot IDs without running analysis.
	 *
	 * @return array<string, mixed>
	 */
	private function comparison_selection() {
		$before_id       = $this->requested_snapshot_id( 'before_snapshot' );
		$after_id        = $this->requested_snapshot_id( 'after_snapshot' );
		$before_snapshot = null;
		$after_snapshot  = null;
		$notice          = '';

		if ( '' === $before_id && '' === $after_id ) {
			$notice = 'incomplete';
		} elseif ( '' === $before_id || '' === $after_id ) {
			$notice = 'incomplete';
		} elseif ( $before_id === $after_id ) {
			$notice = 'same';
		} elseif ( ! wp_is_uuid( $before_id ) || ! wp_is_uuid( $after_id ) ) {
			$notice = 'invalid';
		} else {
			$before_snapshot = $this->snapshots->find( $before_id );
			$after_snapshot  = $this->snapshots->find( $after_id );

			if ( null === $before_snapshot || null === $after_snapshot ) {
				$notice = 'invalid';
			}
		}

		return array(
			'before_id' => $before_id,
			'after_id'  => $after_id,
			'notice'    => $notice,
			'before_snapshot' => $before_snapshot,
			'after_snapshot'  => $after_snapshot,
		);
	}

	/**
	 * Gets one sanitized snapshot ID from the comparison query.
	 *
	 * @param string $key Query key.
	 * @return string
	 */
	private function requested_snapshot_id( $key ) {
		return isset( $_GET[ $key ] ) ? sanitize_text_field( wp_unslash( $_GET[ $key ] ) ) : '';
	}

	/**
	 * Renders snapshot options for one comparison selector.
	 *
	 * @param array  $snapshots Stored snapshots.
	 * @param string $selected_id Selected snapshot ID.
	 * @return void
	 */
	private function render_snapshot_options( array $snapshots, $selected_id ) {
		?>
		<option value=""><?php echo esc_html__( 'Select a snapshot', 'acf-schema-guard' ); ?></option>
		<?php foreach ( $snapshots as $snapshot ) : ?>
			<option value="<?php echo esc_attr( $snapshot->id() ); ?>" <?php selected( $selected_id, $snapshot->id() ); ?>>
				<?php echo esc_html( $snapshot->created_at() . ' - ' . $snapshot->source_id() . ' - ' . $snapshot->id() ); ?>
			</option>
		<?php endforeach; ?>
		<?php
	}

	/**
	 * Renders a validation notice for the submitted comparison selection.
	 *
	 * @param string $notice Notice key.
	 * @return void
	 */
	private function render_comparison_notice( $notice ) {
		$messages = array(
			'incomplete' => __( 'Select both snapshots to run a comparison.', 'acf-schema-guard' ),
			'same'       => __( 'Select two different snapshots to run a comparison.', 'acf-schema-guard' ),
			'invalid'    => __( 'The selected snapshots are not available. Choose snapshots from the list and try again.', 'acf-schema-guard' ),
		);

		if ( isset( $messages[ $notice ] ) ) {
			?>
			<div class="notice notice-warning inline"><p><?php echo esc_html( $messages[ $notice ] ); ?></p></div>
			<?php
		}
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
		?>
		<table class="widefat striped acf-schema-guard-findings">
			<thead><tr>
				<th scope="col"><?php echo esc_html__( 'Kind', 'acf-schema-guard' ); ?></th>
				<th scope="col"><?php echo esc_html__( 'Node type', 'acf-schema-guard' ); ?></th>
				<th scope="col"><?php echo esc_html__( 'Path', 'acf-schema-guard' ); ?></th>
				<th scope="col"><?php echo esc_html__( 'Severity', 'acf-schema-guard' ); ?></th>
				<th scope="col"><?php echo esc_html__( 'Rationale', 'acf-schema-guard' ); ?></th>
			</tr></thead>
			<tbody>
				<?php foreach ( $analysis['findings'] as $finding ) : ?>
					<?php $change = $finding['change']; ?>
					<tr>
						<td><?php echo esc_html( $change['kind'] ); ?></td>
						<td><?php echo esc_html( $change['node_type'] ); ?></td>
						<td><code><?php echo esc_html( implode( '.', $change['path'] ) ); ?></code></td>
						<td><span class="acf-schema-guard-severity acf-schema-guard-severity-<?php echo esc_attr( $finding['severity'] ); ?>"><?php echo esc_html( $finding['severity'] ); ?></span></td>
						<td><?php echo esc_html( $finding['rationale'] ); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
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
