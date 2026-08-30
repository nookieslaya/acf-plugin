<?php
/**
 * Theme header.
 *
 * @package ACFSchemaGuardDev
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<header class="site-header">
	<a class="site-title" href="<?php echo esc_url( home_url( '/' ) ); ?>">
		<?php echo esc_html( get_bloginfo( 'name' ) ); ?>
	</a>
	<nav aria-label="<?php echo esc_attr__( 'Primary navigation', 'acf-schema-guard-dev' ); ?>">
		<?php
		wp_nav_menu(
			array(
				'theme_location' => 'primary',
				'fallback_cb'    => false,
			)
		);
		?>
	</nav>
</header>
