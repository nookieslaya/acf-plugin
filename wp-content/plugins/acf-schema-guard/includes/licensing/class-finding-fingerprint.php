<?php
/**
 * Produces a stable identity for one classified schema finding.
 *
 * @package ACFSchemaGuard
 */

namespace AcfSchemaGuard\Licensing;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class FindingFingerprint {
	public static function from_finding( array $finding ) {
		$change = isset( $finding['change'] ) && is_array( $finding['change'] ) ? $finding['change'] : array();
		$data   = array(
			'kind'      => isset( $change['kind'] ) ? (string) $change['kind'] : '',
			'node_type' => isset( $change['node_type'] ) ? (string) $change['node_type'] : '',
			'path'      => isset( $change['path'] ) && is_array( $change['path'] ) ? array_values( $change['path'] ) : array(),
			'before'    => isset( $change['before'] ) ? $change['before'] : null,
			'after'     => isset( $change['after'] ) ? $change['after'] : null,
		);

		return hash( 'sha256', wp_json_encode( $data ) );
	}
}
