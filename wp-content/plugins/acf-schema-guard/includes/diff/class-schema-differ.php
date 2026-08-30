<?php
namespace AcfSchemaGuard\Diff;
if ( ! defined( 'ABSPATH' ) ) { exit; }
final class SchemaDiffer {
	public function compare( array $before, array $after ) {
		$changes = array();
		$left = $this->map( isset( $before['field_groups'] ) ? $before['field_groups'] : array() );
		$right = $this->map( isset( $after['field_groups'] ) ? $after['field_groups'] : array() );
		foreach ( array_unique( array_merge( array_keys( $left ), array_keys( $right ) ) ) as $key ) {
			if ( ! isset( $left[ $key ] ) ) { $changes[] = new SchemaChange( 'added', 'field_group', array( $key ), null, $right[ $key ] ); }
			elseif ( ! isset( $right[ $key ] ) ) { $changes[] = new SchemaChange( 'removed', 'field_group', array( $key ), $left[ $key ], null ); }
			elseif ( $left[ $key ] !== $right[ $key ] ) { $changes[] = new SchemaChange( 'modified', 'field_group', array( $key ), $left[ $key ], $right[ $key ] ); $changes = array_merge( $changes, $this->field_changes( $left[ $key ], $right[ $key ], array( $key ) ) ); }
		}
		return new SchemaDiff( $changes );
	}
	private function field_changes( array $before, array $after, array $path ) { $changes = array(); $left = $this->map( isset( $before['fields'] ) ? $before['fields'] : array() ); $right = $this->map( isset( $after['fields'] ) ? $after['fields'] : array() ); foreach ( array_unique( array_merge( array_keys( $left ), array_keys( $right ) ) ) as $key ) { $node_path = array_merge( $path, array( $key ) ); if ( ! isset( $left[$key] ) ) { $changes[] = new SchemaChange('added','field',$node_path,null,$right[$key]); } elseif ( ! isset( $right[$key] ) ) { $changes[] = new SchemaChange('removed','field',$node_path,$left[$key],null); } elseif ( $left[$key] !== $right[$key] ) { $changes[] = new SchemaChange('modified','field',$node_path,$left[$key],$right[$key]); $changes = array_merge($changes,$this->field_changes($left[$key],$right[$key],$node_path)); } } return $changes; }
	private function map( array $nodes ) { $map = array(); foreach ( $nodes as $node ) { if ( is_array( $node ) && isset( $node['key'] ) ) { $map[ $node['key'] ] = $node; } } ksort( $map, SORT_STRING ); return $map; }
}
