<?php
/**
 * Hero fixture using top-level ACF fields.
 *
 * @package ACFSchemaGuardDev
 */

if ( ! defined( 'ABSPATH' ) || ! function_exists( 'get_field' ) || ! function_exists( 'get_field_object' ) ) {
	return;
}
$hero_title       = get_field( 'hero_title21' );
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
		<h1 class="entry-title"><?php echo esc_html( get_field( 'hero_title_zmiana' )); ?></h1>
		<h2 class="entry-title"><?php echo esc_html( $hero_title ); ?></h2>
	<?php endif; ?>
	<?php if ( $hero_text ) : ?>
		<div><?php echo wp_kses_post( wpautop( $hero_text ) ); ?></div>
	<?php endif; ?>
	<?php if ( is_array( $hero_cta ) && ! empty( $hero_cta['url'] ) ) : ?>
		<p><a href="<?php echo esc_url( $hero_cta['url'] ); ?>"<?php echo ! empty( $hero_cta['target'] ) ? ' target="' . esc_attr( $hero_cta['target'] ) . '"' : ''; ?>><?php echo esc_html( $hero_cta['title'] ?? '' ); ?></a></p>
	<?php endif; ?>
</section>
<style>

	.entry {
		padding: 2rem;
		background-color: #f5f5f5;
		text-align: center;
	}
	.entry img {
		max-width: 100%;
		height: auto;
		margin-bottom: 1rem;
	}
	.entry-title {
		font-size: 2rem;
		margin-bottom: 1rem;
	}
	.entry p {
		margin-top: 1rem;
	}

	.entry a {
		display: inline-block;
		padding: 0.5rem 1rem;
		background-color: #0073aa;
		color: #fff;
		text-decoration: none;
		border-radius: 4px;
	}
</style>
