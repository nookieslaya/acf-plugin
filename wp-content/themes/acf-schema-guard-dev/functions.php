<?php
/**
 * Theme bootstrap for ACF Schema Guard Dev.
 *
 * @package ACFSchemaGuardDev
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once get_theme_file_path( 'inc/acf-options.php' );

/**
 * Configures the development theme's WordPress supports.
 */
function acf_schema_guard_dev_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support(
		'html5',
		array(
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'script',
			'style',
		)
	);

	register_nav_menus(
		array(
			'primary' => __( 'Primary menu', 'acf-schema-guard-dev' ),
		)
	);
}
add_action( 'after_setup_theme', 'acf_schema_guard_dev_setup' );

/**
 * Enqueues the small development stylesheet.
 */
function acf_schema_guard_dev_enqueue_assets() {
	wp_enqueue_style(
		'acf-schema-guard-dev',
		get_stylesheet_uri(),
		array(),
		wp_get_theme()->get( 'Version' )
	);
}
add_action( 'wp_enqueue_scripts', 'acf_schema_guard_dev_enqueue_assets' );
