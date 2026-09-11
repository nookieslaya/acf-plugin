<?php
/**
 * Read-only access to schema-derived WordPress post-meta impact evidence.
 *
 * @package ACFSchemaGuard
 */

namespace AcfSchemaGuard\Impact;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

interface StoredDataImpactRepository {
	/**
	 * @param StoredDataImpactMatcher $matcher Schema-derived meta-key matcher.
	 * @param int    $limit Maximum safe record sample size.
	 * @return array{record_count:int,records:array[]}
	 */
	public function find( StoredDataImpactMatcher $matcher, $limit );
}
