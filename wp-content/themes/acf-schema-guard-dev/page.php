<?php
/**
 * Page template.
 *
 * @package ACFSchemaGuardDev
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<main class="site-main">
	<?php while ( have_posts() ) : ?>
		<?php the_post(); ?>
		<?php get_template_part( 'template-parts/content', 'page' ); ?>
	<?php endwhile; ?>
</main>
<?php get_footer(); ?>
