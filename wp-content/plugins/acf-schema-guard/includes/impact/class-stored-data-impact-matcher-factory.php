<?php
/**
 * Builds only provable ACF post-meta matchers from pre-change schema nodes.
 *
 * @package ACFSchemaGuard
 */

namespace AcfSchemaGuard\Impact;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class StoredDataImpactMatcherFactory {
	public function for_change( array $change ) {
		if ( 'field' === $this->value( $change, 'node_type' ) && $this->is_destructive_field_change( $change ) ) {
			return array( $this->for_field( $change['before'], $this->ancestors( $change ) ) );
		}

		if ( 'field_group' === $this->value( $change, 'node_type' ) && 'removed' === $this->value( $change, 'kind' ) && ! empty( $change['before'] ) && is_array( $change['before'] ) ) {
			return $this->for_fields( $this->children( $change['before'] ), array() );
		}

		return array();
	}

	private function for_fields( array $fields, array $ancestors ) {
		$matchers = array();

		foreach ( $fields as $field ) {
			if ( ! is_array( $field ) || '' === $this->value( $field, 'name' ) ) {
				continue;
			}

			$matchers[] = $this->for_field( $field, $ancestors );
			$descendant_ancestors = array_merge( $ancestors, array( $this->ancestor( $field ) ) );
			$matchers = array_merge( $matchers, $this->for_fields( $this->sub_fields( $field ), $descendant_ancestors ) );

			foreach ( $this->layouts( $field ) as $layout ) {
				if ( is_array( $layout ) ) {
					$matchers = array_merge( $matchers, $this->for_fields( $this->sub_fields( $layout ), $descendant_ancestors ) );
				}
			}
		}

		return $matchers;
	}

	private function for_field( array $field, array $ancestors ) {
		$name = $this->value( $field, 'name' );
		$path = array();

		foreach ( $ancestors as $ancestor ) {
			$path[] = $this->value( $ancestor, 'name' );
		}
		$path[] = $name;

		if ( empty( $ancestors ) ) {
			return new StoredDataImpactMatcher( $name, $path, $name, 'exact', $name, 'direct' );
		}

		$segments = array();
		foreach ( $ancestors as $ancestor ) {
			$ancestor_name = $this->value( $ancestor, 'name' );
			$ancestor_type = $this->value( $ancestor, 'type' );
			if ( ! $this->is_name( $ancestor_name ) || ! in_array( $ancestor_type, array( 'group', 'repeater', 'flexible_content' ), true ) ) {
				return new StoredDataImpactMatcher( $name, $path, '', 'none', '', 'unknown' );
			}

			$segments[] = $ancestor_name;
			if ( in_array( $ancestor_type, array( 'repeater', 'flexible_content' ), true ) ) {
				$segments[] = '{row}';
			}
		}

		if ( ! $this->is_name( $name ) ) {
			return new StoredDataImpactMatcher( $name, $path, '', 'none', '', 'unknown' );
		}

		$segments[] = $name;
		$pattern    = implode( '_', $segments );
		$expression = '^' . implode( '_', array_map( array( $this, 'regex_segment' ), $segments ) ) . '$';

		return new StoredDataImpactMatcher( $name, $path, $pattern, 'regexp', $expression, 'nested' );
	}

	private function is_destructive_field_change( array $change ) {
		if ( 'removed' === $this->value( $change, 'kind' ) ) {
			return ! empty( $change['before']['name'] );
		}

		return 'modified' === $this->value( $change, 'kind' )
			&& isset( $change['before']['name'], $change['after']['name'] )
			&& $change['before']['name'] !== $change['after']['name'];
	}

	private function ancestors( array $change ) {
		return isset( $change['context']['ancestors'] ) && is_array( $change['context']['ancestors'] ) ? $change['context']['ancestors'] : array();
	}

	private function ancestor( array $field ) {
		return array( 'name' => $this->value( $field, 'name' ), 'type' => $this->value( $field, 'type' ) );
	}

	private function children( array $node ) {
		return isset( $node['fields'] ) && is_array( $node['fields'] ) ? $node['fields'] : array();
	}

	private function sub_fields( array $node ) {
		return isset( $node['sub_fields'] ) && is_array( $node['sub_fields'] ) ? $node['sub_fields'] : array();
	}

	private function layouts( array $node ) {
		return isset( $node['layouts'] ) && is_array( $node['layouts'] ) ? $node['layouts'] : array();
	}

	private function value( array $source, $key ) {
		return isset( $source[ $key ] ) ? (string) $source[ $key ] : '';
	}

	private function is_name( $name ) {
		return 1 === preg_match( '/^[A-Za-z0-9_-]+$/', (string) $name );
	}

	private function regex_segment( $segment ) {
		return '{row}' === $segment ? '[0-9]+' : preg_quote( $segment, '/' );
	}
}
