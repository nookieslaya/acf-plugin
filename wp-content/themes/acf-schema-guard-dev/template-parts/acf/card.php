<?php
/**
 * Card fixture using top-level ACF fields.
 *
 * @package ACFSchemaGuardDev
 */

if ( ! defined( 'ABSPATH' ) || ! function_exists( 'get_field' ) || ! function_exists( 'the_field' ) ) {
	return;
}

$card_title = get_field( 'card_title' );
$card_image = get_field( 'card_image' );
$card_link  = get_field( 'card_link' );

ob_start();
the_field( 'card_text' );
$card_text = ob_get_clean();

if ( ! $card_title && ! $card_text && ! $card_image && ! $card_link ) {
	return;
}
?>
<section class="entry">
	<?php if ( is_array( $card_image ) && ! empty( $card_image['url'] ) ) : ?>
		<img src="<?php echo esc_url( $card_image['url'] ); ?>" alt="<?php echo esc_attr( $card_image['alt'] ?? '' ); ?>">
	<?php endif; ?>
	<?php if ( $card_title ) : ?>
		<h2 class="entry-title"><?php echo esc_html( $card_title ); ?></h2>
	<?php endif; ?>
	<?php if ( $card_text ) : ?>
		<div><?php echo wp_kses_post( wpautop( $card_text ) ); ?></div>
	<?php endif; ?>
	<?php if ( is_array( $card_link ) && ! empty( $card_link['url'] ) ) : ?>
		<p><a href="<?php echo esc_url( $card_link['url'] ); ?>"<?php echo ! empty( $card_link['target'] ) ? ' target="' . esc_attr( $card_link['target'] ) . '"' : ''; ?>><?php echo esc_html( $card_link['title'] ?? '' ); ?></a></p>
	<?php endif; ?>
</section>
