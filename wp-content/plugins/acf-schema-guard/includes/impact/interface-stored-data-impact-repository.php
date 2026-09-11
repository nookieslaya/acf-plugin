<?php
/**
 * Read-only access to direct WordPress post-meta impact evidence.
 *
 * @package ACFSchemaGuard
 */

namespace AcfSchemaGuard\Impact;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

interface StoredDataImpactRepository {
	/**
	 * @param string $field_name Exact ACF field name.
	 * @param int    $limit Maximum safe record sample size.
	 * @return array{record_count:int,records:array[]}
	 */
	public function find( $field_name, $limit );
}
