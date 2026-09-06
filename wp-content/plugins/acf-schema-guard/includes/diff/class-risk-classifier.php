<?php
/**
 * Classifies normalized schema changes by deployment risk.
 *
 * @package ACFSchemaGuard
 */

namespace AcfSchemaGuard\Diff;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class RiskClassifier {
	/**
	 * Converts every schema change into a risk finding.
	 *
	 * @param SchemaDiff $diff Schema changes to classify.
	 * @return RiskFinding[]
	 */
	public function classify( SchemaDiff $diff ) {
		$findings = array();

		foreach ( $diff->to_array()['changes'] as $data ) {
			$change = new SchemaChange(
				$data['kind'],
				$data['node_type'],
				$data['path'],
				$data['before'],
				$data['after']
			);
			list( $severity, $rationale ) = $this->classification( $data );
			$findings[] = new RiskFinding( $change, $severity, $rationale );
		}

		return $findings;
	}

	/**
	 * Determines one change's severity and rationale.
	 *
	 * @param array $change Schema change data.
	 * @return string[]
	 */
	private function classification( array $change ) {
		if ( 'added' === $change['kind'] ) {
			return array( 'safe', 'Schema node was added.' );
		}

		if ( 'removed' === $change['kind'] ) {
			return array( 'critical', 'Schema node was removed.' );
		}

		$before = is_array( $change['before'] ) ? $change['before'] : array();
		$after  = is_array( $change['after'] ) ? $change['after'] : array();

		if ( isset( $before['type'], $after['type'] ) && $before['type'] !== $after['type'] ) {
			return array( 'high', 'Field type changed.' );
		}

		if ( 'field' === $change['node_type'] && isset( $before['name'], $after['name'] ) && $before['name'] !== $after['name'] ) {
			return array( 'high', 'Field name changed.' );
		}

		return array( 'warning', 'Schema node changed.' );
	}
}
