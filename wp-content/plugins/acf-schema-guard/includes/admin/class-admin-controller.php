<?php
/**
 * Registers the read-only WordPress Admin foundation.
 *
 * @package ACFSchemaGuard
 */

namespace AcfSchemaGuard\Admin;

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

	/**
	 * Registers the WordPress Admin menu hook.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'admin_menu', array( $this, 'register_menus' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
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

		$screen = $this->current_screen();

		if ( null === $screen ) {
			wp_die( esc_html__( 'The requested ACF Schema Guard page is not available.', 'acf-schema-guard' ) );
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
	private function current_screen() {
		$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';

		return isset( self::$screens[ $page ] ) ? self::$screens[ $page ] : null;
	}
}
