<?php
/**
 * Formats classified findings for shared WP-CLI table output.
 *
 * @package ACFSchemaGuard
 */

namespace AcfSchemaGuard\Cli;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class FindingOutputFormatter {
	/**
	 * @param array[] $findings Classified findings.
	 * @return array[]
	 */
	public function table_items( array $findings ) {
		$items = array();

		foreach ( $findings as $finding ) {
			if ( ! is_array( $finding ) || ! isset( $finding['change'] ) || ! is_array( $finding['change'] ) ) {
				continue;
			}

			$change      = $finding['change'];
			$explanation = isset( $finding['explanation'] ) && is_array( $finding['explanation'] ) ? $finding['explanation'] : array();
			$items[]     = array(
				'kind'      => isset( $change['kind'] ) ? (string) $change['kind'] : '',
				'node_type' => isset( $change['node_type'] ) ? (string) $change['node_type'] : '',
				'path'      => isset( $change['path'] ) && is_array( $change['path'] ) ? implode( '.', $change['path'] ) : '',
				'summary'   => isset( $explanation['summary'] ) ? (string) $explanation['summary'] : '',
				'details'   => $this->details( isset( $explanation['details'] ) ? $explanation['details'] : array() ),
				'severity'  => isset( $finding['severity'] ) ? (string) $finding['severity'] : '',
				'rationale' => isset( $finding['rationale'] ) ? (string) $finding['rationale'] : '',
			);
		}

		return $items;
	}

	/**
	 * @return string[]
	 */
	public function table_fields() {
		return array( 'kind', 'node_type', 'path', 'summary', 'details', 'severity', 'rationale' );
	}

	/**
	 * @param mixed $details Explanation details.
	 * @return string
	 */
	private function details( $details ) {
		if ( ! is_array( $details ) ) {
			return '';
		}

		$lines = array();
		foreach ( $details as $detail ) {
			if ( is_scalar( $detail ) ) {
				$lines[] = (string) $detail;
			}
		}

		return implode( '; ', $lines );
	}
}
