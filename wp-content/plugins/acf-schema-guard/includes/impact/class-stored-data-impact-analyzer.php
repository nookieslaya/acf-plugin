<?php
/**
 * Links destructive ACF changes to direct WordPress post-meta evidence.
 *
 * @package ACFSchemaGuard
 */

namespace AcfSchemaGuard\Impact;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class StoredDataImpactAnalyzer {
	const RECORD_LIMIT = 20;

	private $repository;

	public function __construct( StoredDataImpactRepository $repository ) {
		$this->repository = $repository;
	}

	public function analyze( array $changes ) {
		$impacts = array();

		foreach ( $changes as $change ) {
			foreach ( $this->affected_field_names( $change ) as $field_name ) {
				$evidence = $this->repository->find( $field_name, self::RECORD_LIMIT );
				$impacts[] = new StoredDataImpact(
					$change,
					$field_name,
					isset( $evidence['record_count'] ) ? $evidence['record_count'] : 0,
					isset( $evidence['records'] ) && is_array( $evidence['records'] ) ? $evidence['records'] : array()
				);
			}
		}

		return $impacts;
	}

	private function affected_field_names( $change ) {
		if ( ! is_array( $change ) || empty( $change['kind'] ) || empty( $change['node_type'] ) ) {
			return array();
		}
		if ( 'field' === $change['node_type'] && $this->is_removed_or_renamed( $change ) && ! empty( $change['before']['name'] ) ) {
			return array( (string) $change['before']['name'] );
		}
		if ( 'field_group' === $change['node_type'] && 'removed' === $change['kind'] && ! empty( $change['before'] ) && is_array( $change['before'] ) ) {
			return array_values( array_unique( $this->field_names( $change['before'] ) ) );
		}

		return array();
	}

	private function is_removed_or_renamed( array $change ) {
		if ( 'removed' === $change['kind'] ) {
			return true;
		}

		return 'modified' === $change['kind']
			&& isset( $change['before']['name'], $change['after']['name'] )
			&& $change['before']['name'] !== $change['after']['name'];
	}

	private function field_names( array $node ) {
		$names = ! empty( $node['name'] ) ? array( (string) $node['name'] ) : array();

		foreach ( array( 'fields', 'sub_fields', 'layouts' ) as $key ) {
			if ( empty( $node[ $key ] ) || ! is_array( $node[ $key ] ) ) {
				continue;
			}
			foreach ( $node[ $key ] as $child ) {
				if ( is_array( $child ) ) {
					$names = array_merge( $names, $this->field_names( $child ) );
				}
			}
		}

		return $names;
	}
}
