<?php
/**
 * Fallback template.
 *
 * @package ACFSchemaGuardDev
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<main class="site-main">
	<?php if ( have_posts() ) : ?>
		<?php while ( have_posts() ) : ?>
			<?php the_post(); ?>
			<?php get_template_part( 'template-parts/content', get_post_type() ); ?>
		<?php endwhile; ?>
	<?php else : ?>
		<section class="empty-state">
			<h1><?php echo esc_html__( 'No content found', 'acf-schema-guard-dev' ); ?></h1>
		</section>
	<?php endif; ?>
</main>
<?php get_footer(); ?>
