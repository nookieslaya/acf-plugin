<?php
namespace AcfSchemaGuard\Diff;
if ( ! defined( 'ABSPATH' ) ) { exit; }
final class SchemaDiffer {
	public function compare( array $before, array $after ) {
		return new SchemaDiff( $this->compare_nodes( $this->map( isset( $before['field_groups'] ) ? $before['field_groups'] : array(), true ), $this->map( isset( $after['field_groups'] ) ? $after['field_groups'] : array(), true ), array(), 'field_group', array() ) );
	}
	private function compare_nodes( array $left, array $right, array $path, $type, array $ancestors ) {
		$changes = array();
		foreach ( array_unique( array_merge( array_keys( $left ), array_keys( $right ) ) ) as $key ) {
			$node_path = array_merge( $path, array( $key ) );
			$context = 'field' === $type ? array( 'ancestors' => $ancestors ) : array();
			if ( ! isset( $left[ $key ] ) ) {
				$changes[] = new SchemaChange( 'added', $type, $node_path, null, $right[ $key ], $context );
			} elseif ( ! isset( $right[ $key ] ) ) {
				$changes[] = new SchemaChange( 'removed', $type, $node_path, $left[ $key ], null, $context );
			} elseif ( $left[ $key ] !== $right[ $key ] ) {
				$changes[] = new SchemaChange( 'modified', $type, $node_path, $left[ $key ], $right[ $key ], $context );
				$changes = array_merge( $changes, $this->children( $left[ $key ], $right[ $key ], $node_path, $ancestors ) );
			}
		}
		return $changes;
	}
	private function children( array $before, array $after, array $path, array $ancestors ) {
		$child_ancestors = $ancestors;
		if ( isset( $before['type'] ) ) {
			$child_ancestors[] = array(
				'name' => isset( $before['name'] ) ? (string) $before['name'] : '',
				'type' => (string) $before['type'],
			);
		}

		$changes = $this->compare_nodes(
			$this->map( isset( $before['sub_fields'] ) ? $before['sub_fields'] : ( isset( $before['fields'] ) ? $before['fields'] : array() ) ),
			$this->map( isset( $after['sub_fields'] ) ? $after['sub_fields'] : ( isset( $after['fields'] ) ? $after['fields'] : array() ) ),
			$path,
			'field',
			$child_ancestors
		);
		$left = $this->map( isset( $before['layouts'] ) ? $before['layouts'] : array() );
		$right = $this->map( isset( $after['layouts'] ) ? $after['layouts'] : array() );
		foreach ( array_intersect_key( $left, $right ) as $key => $layout ) {
			$changes = array_merge( $changes, $this->compare_nodes( $this->map( isset( $layout['sub_fields'] ) ? $layout['sub_fields'] : array() ), $this->map( isset( $right[ $key ]['sub_fields'] ) ? $right[ $key ]['sub_fields'] : array() ), array_merge( $path, array( $key ) ), 'field', $child_ancestors ) );
		}
		return $changes;
	}
	private function map( array $nodes, $exclude_trashed_groups = false ) {
		$map = array();
		foreach ( $nodes as $node ) {
			if ( $exclude_trashed_groups && is_array( $node ) && isset( $node['key'] ) && $this->is_trashed_group_key( $node['key'] ) ) {
				continue;
			}

			if ( is_array( $node ) && isset( $node['key'] ) ) {
				$map[ $node['key'] ] = $node;
			}
		}
		ksort( $map, SORT_STRING );
		return $map;
	}
	private function is_trashed_group_key( $key ) {
		return 0 === strpos( (string) $key, 'group_' ) && '__trashed' === substr( (string) $key, -9 );
	}
}
