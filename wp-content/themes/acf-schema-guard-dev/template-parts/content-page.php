<?php
/**
 * Page content template.
 *
 * @package ACFSchemaGuardDev
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_template_part( 'template-parts/acf/hero' );
get_template_part( 'template-parts/acf/features' );
get_template_part( 'template-parts/acf/card' );
get_template_part( 'template-parts/acf/flexible-content' );
get_template_part( 'template-parts/content' );
