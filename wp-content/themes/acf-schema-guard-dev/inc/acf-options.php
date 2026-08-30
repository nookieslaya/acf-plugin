<?php
/**
 * ACF PRO-only options page used by the development fixtures.
 *
 * @package ACFSchemaGuardDev
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the optional ACF PRO settings page when its API is available.
 */
function acf_schema_guard_dev_register_options_page() {
	if ( ! function_exists( 'acf_add_options_page' ) ) {
		return;
	}

	acf_add_options_page(
		array(
			'page_title' => __( 'Schema Guard Test Settings', 'acf-schema-guard-dev' ),
			'menu_title' => __( 'Schema Guard Test', 'acf-schema-guard-dev' ),
			'menu_slug'  => 'acf-schema-guard-test-settings',
			'capability' => 'manage_options',
			'redirect'   => false,
		)
	);
}
add_action( 'acf/init', 'acf_schema_guard_dev_register_options_page' );
