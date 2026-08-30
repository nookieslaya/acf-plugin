<?php
/**
 * Read-only boundary for complete ACF schema data.
 *
 * @package ACFSchemaGuard
 */

namespace AcfSchemaGuard\Acf;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Supplies field groups with their full field definitions.
 */
interface FullSchemaSource {
	/**
	 * Gets raw field groups for normalization.
	 *
	 * @return array[] Each group includes its fields.
	 */
	public function field_groups();
}
