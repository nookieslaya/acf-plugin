<?php
/**
 * Flexible Content fixture using layout-specific sub fields.
 *
 * @package ACFSchemaGuardDev
 */

if ( ! defined( 'ABSPATH' ) || ! function_exists( 'have_rows' ) || ! function_exists( 'get_sub_field' ) ) {
	return;
}

if ( ! have_rows( 'page_sections' ) ) {
	return;
}
?>
<section aria-label="<?php echo esc_attr__( 'Flexible page sections', 'acf-schema-guard-dev' ); ?>">
	<?php while ( have_rows( 'page_sections' ) ) : ?>
		<?php the_row(); ?>
		<?php if ( 'hero' === get_row_layout() ) : ?>
			<?php $section_title = get_sub_field( 'hero_title' ); ?>
			<section class="entry">
				<?php if ( $section_title ) : ?>
					<h2 class="entry-title"><?php echo esc_html( $section_title ); ?></h2>
				<?php endif; ?>
				<?php echo wp_kses_post( wpautop( get_sub_field( 'hero_text' ) ) ); ?>
			</section>
		<?php elseif ( 'text_section' === get_row_layout() ) : ?>
			<?php $section_heading = get_sub_field( 'section_heading' ); ?>
			<section class="entry">
				<?php if ( $section_heading ) : ?>
					<h2 class="entry-title"><?php echo esc_html( $section_heading ); ?></h2>
				<?php endif; ?>
				<?php echo wp_kses_post( get_sub_field( 'section_content' ) ); ?>
			</section>
		<?php elseif ( 'cards' === get_row_layout() && have_rows( 'cards' ) ) : ?>
			<section class="entry">
				<?php $cards_heading = get_sub_field( 'cards_heading' ); ?>
				<?php if ( $cards_heading ) : ?>
					<h2 class="entry-title"><?php echo esc_html( $cards_heading ); ?></h2>
				<?php endif; ?>
				<ul>
					<?php while ( have_rows( 'cards' ) ) : ?>
						<?php the_row(); ?>
						<li>
							<strong><?php echo esc_html( get_sub_field( 'card_title' ) ); ?></strong>
							<div><?php echo wp_kses_post( wpautop( get_sub_field( 'card_text' ) ) ); ?></div>
						</li>
					<?php endwhile; ?>
				</ul>
			</section>
		<?php endif; ?>
	<?php endwhile; ?>
</section>
