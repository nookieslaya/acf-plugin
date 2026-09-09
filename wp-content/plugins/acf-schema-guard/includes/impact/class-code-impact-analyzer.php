<?php
/**
 * Finds PHP ACF references affected by schema changes.
 *
 * @package ACFSchemaGuard
 */

namespace AcfSchemaGuard\Impact;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class CodeImpactAnalyzer {
	public function analyze( array $changes, array $references ) {
		$impacts = array();
		$seen    = array();

		foreach ( $changes as $change ) {
			$field_names = $this->affected_field_names( $change );
			if ( empty( $field_names ) ) {
				continue;
			}

			foreach ( $references as $reference ) {
				$reference_data = $this->reference_data( $reference );

				if ( ! in_array( $this->reference_field_name( $reference_data ), $field_names, true ) ) {
					continue;
				}

				$key = $this->impact_key( $change, $reference_data );
				if ( isset( $seen[ $key ] ) ) {
					continue;
				}

				$seen[ $key ] = true;
				$impacts[]    = new CodeImpact(
					$change,
					$reference_data,
					$this->severity( $change ),
					'Review this PHP ACF reference.'
				);
			}
		}

		return $impacts;
	}

	private function affected_field_names( $change ) {
		if ( $this->is_supported_field_change( $change ) ) {
			return array( $change['before']['name'] );
		}
		if ( ! is_array( $change ) || 'removed' !== $change['kind'] || 'field_group' !== $change['node_type'] || empty( $change['before'] ) ) {
			return array();
		}
		return $this->field_names( $change['before'] );
	}

	private function field_names( array $node ) {
		$names = ! empty( $node['name'] ) ? array( $node['name'] ) : array();
		foreach ( array( 'fields', 'sub_fields', 'layouts' ) as $key ) {
			if ( empty( $node[ $key ] ) || ! is_array( $node[ $key ] ) ) { continue; }
			foreach ( $node[ $key ] as $child ) { if ( is_array( $child ) ) { $names = array_merge( $names, $this->field_names( $child ) ); } }
		}
		return $names;
	}

	private function is_supported_field_change( $change ) {
		return is_array( $change )
			&& isset( $change['node_type'], $change['before'] )
			&& 'field' === $change['node_type']
			&& is_array( $change['before'] )
			&& ! empty( $change['before']['name'] );
	}

	private function reference_data( $reference ) {
		if ( is_object( $reference ) && method_exists( $reference, 'to_array' ) ) {
			return $reference->to_array();
		}

		return is_array( $reference ) ? $reference : array();
	}

	private function reference_field_name( array $reference ) {
		return isset( $reference['field_name'] ) ? (string) $reference['field_name'] : '';
	}

	private function impact_key( array $change, array $reference ) {
		return implode(
			'|',
			array(
				(string) $change['kind'],
				isset( $change['before']['name'] ) ? (string) $change['before']['name'] : implode( '.', $change['path'] ),
				$this->reference_field_name( $reference ),
				isset( $reference['path'] ) ? (string) $reference['path'] : '',
				isset( $reference['line'] ) ? (string) $reference['line'] : '',
				isset( $reference['expression'] ) ? (string) $reference['expression'] : '',
			)
		);
	}

	private function severity( array $change ) {
		if ( 'removed' === $change['kind'] ) {
			return 'critical';
		}

		if (
			isset( $change['after'] )
			&& is_array( $change['after'] )
			&& ! empty( $change['after']['name'] )
			&& $change['before']['name'] !== $change['after']['name']
		) {
			return 'high';
		}

		return 'warning';
	}
}
