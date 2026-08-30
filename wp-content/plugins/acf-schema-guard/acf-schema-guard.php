<?php
/**
 * Plugin Name:       ACF Schema Guard
 * Description:       Detects potentially breaking Advanced Custom Fields schema changes before deployment.
 * Version:           0.1.0
 * Author:            ACF Schema Guard
 * Text Domain:       acf-schema-guard
 *
 * @package ACFSchemaGuard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ACF_SCHEMA_GUARD_VERSION', '0.1.0' );
define( 'ACF_SCHEMA_GUARD_FILE', __FILE__ );
define( 'ACF_SCHEMA_GUARD_PATH', plugin_dir_path( __FILE__ ) );

require_once ACF_SCHEMA_GUARD_PATH . 'includes/class-plugin.php';

add_action(
	'plugins_loaded',
	static function() {
		\AcfSchemaGuard\Plugin::instance()->boot();
	},
	20
);

register_activation_hook(
	ACF_SCHEMA_GUARD_FILE,
	static function() {
		global $wpdb;

		\AcfSchemaGuard\Snapshots\SnapshotTable::install( $wpdb );
	}
);
