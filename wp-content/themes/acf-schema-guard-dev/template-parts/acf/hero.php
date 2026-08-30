<?php
/**
 * Hero fixture using top-level ACF fields.
 *
 * @package ACFSchemaGuardDev
 */

if ( ! defined( 'ABSPATH' ) || ! function_exists( 'get_field' ) || ! function_exists( 'get_field_object' ) ) {
	return;
}

$hero_title       = get_field( 'hero_title' );
$hero_text        = get_field( 'hero_text' );
$hero_image       = get_field( 'hero_image' );
$hero_cta         = get_field( 'hero_cta' );
$hero_title_field = get_field_object( 'hero_title' );

if ( ! $hero_title && ! $hero_text && ! $hero_image && ! $hero_cta ) {
	return;
}

$hero_label = ! empty( $hero_title_field['label'] ) ? $hero_title_field['label'] : __( 'Hero', 'acf-schema-guard-dev' );
?>
<section class="entry" aria-label="<?php echo esc_attr( $hero_label ); ?>">
	<?php if ( is_array( $hero_image ) && ! empty( $hero_image['url'] ) ) : ?>
		<img src="<?php echo esc_url( $hero_image['url'] ); ?>" alt="<?php echo esc_attr( $hero_image['alt'] ?? '' ); ?>">
	<?php endif; ?>
	<?php if ( $hero_title ) : ?>
		<h1 class="entry-title"><?php echo esc_html( $hero_title ); ?></h1>
	<?php endif; ?>
	<?php if ( $hero_text ) : ?>
		<div><?php echo wp_kses_post( wpautop( $hero_text ) ); ?></div>
	<?php endif; ?>
	<?php if ( is_array( $hero_cta ) && ! empty( $hero_cta['url'] ) ) : ?>
		<p><a href="<?php echo esc_url( $hero_cta['url'] ); ?>"<?php echo ! empty( $hero_cta['target'] ) ? ' target="' . esc_attr( $hero_cta['target'] ) . '"' : ''; ?>><?php echo esc_html( $hero_cta['title'] ?? '' ); ?></a></p>
	<?php endif; ?>
</section>
