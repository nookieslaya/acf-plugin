<?php
/**
 * Repeater fixture using ACF sub fields.
 *
 * @package ACFSchemaGuardDev
 */

if ( ! defined( 'ABSPATH' ) || ! function_exists( 'have_rows' ) || ! function_exists( 'get_sub_field' ) || ! function_exists( 'the_sub_field' ) ) {
	return;
}

if ( ! have_rows( 'features' ) ) {
	return;
}
?>
<section class="entry">
	<h2 class="entry-title"><?php echo esc_html__( 'Features', 'acf-schema-guard-dev' ); ?></h2>
	<ul>
		<?php while ( have_rows( 'features' ) ) : ?>
			<?php the_row(); ?>
			<?php
			$feature_title = get_sub_field( 'feature_title' );
			$feature_icon  = get_sub_field( 'feature_icon' );
			ob_start();
			the_sub_field( 'feature_text' );
			$feature_text = ob_get_clean();
			?>
			<li>
				<?php if ( is_array( $feature_icon ) && ! empty( $feature_icon['url'] ) ) : ?>
					<img src="<?php echo esc_url( $feature_icon['url'] ); ?>" alt="<?php echo esc_attr( $feature_icon['alt'] ?? '' ); ?>">
				<?php endif; ?>
				<?php if ( $feature_title ) : ?>
					<strong><?php echo esc_html( $feature_title ); ?></strong>
				<?php endif; ?>
				<?php if ( $feature_text ) : ?>
					<div><?php echo wp_kses_post( wpautop( $feature_text ) ); ?></div>
				<?php endif; ?>
			</li>
		<?php endwhile; ?>
	</ul>
</section>
