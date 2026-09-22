<?php
/**
 * Reads bounded, value-free evidence for a direct ACF field rename.
 *
 * @package ACFSchemaGuard
 */

namespace AcfSchemaGuard\Impact;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

interface RenameDryRunRepository {
	/**
	 * @return array{old_record_count:int,conflict_count:int,records:array[]}
	 */
	public function inspect( $old_name, $new_name, $limit );
}
