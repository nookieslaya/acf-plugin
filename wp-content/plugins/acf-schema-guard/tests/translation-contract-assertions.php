<?php
/**
 * Protects the plugin's extractable Admin translation contract.
 *
 * @package ACFSchemaGuard
 */

function acf_schema_guard_translation_assert( $condition, $message ) {
	if ( ! $condition ) {
		throw new RuntimeException( $message );
	}
}

$plugin_root = dirname( __DIR__ );
$bootstrap   = (string) file_get_contents( $plugin_root . '/acf-schema-guard.php' );
$controller  = (string) file_get_contents( $plugin_root . '/includes/admin/class-admin-controller.php' );
$pot         = (string) file_get_contents( $plugin_root . '/languages/acf-schema-guard.pot' );

acf_schema_guard_translation_assert( false !== strpos( $bootstrap, 'Text Domain:       acf-schema-guard' ), 'Plugin text domain metadata is missing.' );
acf_schema_guard_translation_assert( false !== strpos( $bootstrap, 'Domain Path:       /languages' ), 'Plugin language path metadata is missing.' );
acf_schema_guard_translation_assert( false !== strpos( $bootstrap, "load_plugin_textdomain( 'acf-schema-guard'" ), 'Plugin text domain loader is missing.' );
acf_schema_guard_translation_assert( false === strpos( $controller, "__( \$screen['title']" ), 'Screen titles must not be translated through variables.' );
acf_schema_guard_translation_assert( false === strpos( $controller, "__( \$screen['menu_label']" ), 'Menu labels must not be translated through variables.' );

foreach ( array( 'Overview', 'Changes', 'Field Groups', 'Code Usage', 'Unused Fields', 'History', 'Settings' ) as $label ) {
	acf_schema_guard_translation_assert( false !== strpos( $controller, "__( '" . $label . "', 'acf-schema-guard' )" ), 'Screen label is not an extractable literal: ' . $label );
	acf_schema_guard_translation_assert( false !== strpos( $pot, 'msgid "' . $label . '"' ), 'POT template is missing screen label: ' . $label );
}

echo "Translation contract assertions passed.\n";
