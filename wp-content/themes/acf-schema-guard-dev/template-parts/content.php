<?php
/**
 * Default content template.
 *
 * @package ACFSchemaGuardDev
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry' ); ?>>
	<header>
		<h1 class="entry-title"><?php echo esc_html( get_the_title() ); ?></h1>
	</header>
	<div class="entry-content">
		<?php echo wp_kses_post( apply_filters( 'the_content', get_the_content() ) ); ?>
	</div>
</article>
