<?php
/**
 * Produces deterministic, presentation-neutral schema change descriptions.
 *
 * @package ACFSchemaGuard
 */

namespace AcfSchemaGuard\Diff;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class SchemaChangeExplainer {
	/**
	 * Explains a normalized schema change using core properties only.
	 *
	 * @param array $change SchemaChange::to_array() data.
	 * @return array{summary:string,details:string[]}
	 */
	public function explain( array $change ) {
		$node_type = isset( $change['node_type'] ) ? (string) $change['node_type'] : '';
		$kind      = isset( $change['kind'] ) ? (string) $change['kind'] : '';
		$path      = isset( $change['path'] ) && is_array( $change['path'] ) ? $change['path'] : array();
		$is_nested = 'field' === $node_type && count( $path ) > 2;

		if ( ! in_array( $kind, array( 'added', 'removed', 'modified' ), true ) ) {
			return array(
				'summary' => 'Schema change.',
				'details' => array(),
			);
		}

		$before = isset( $change['before'] ) && is_array( $change['before'] ) ? $change['before'] : array();
		$after  = isset( $change['after'] ) && is_array( $change['after'] ) ? $change['after'] : array();

		if ( 'modified' === $kind ) {
			return array(
				'summary' => $this->summary( $node_type, $kind, $is_nested ),
				'details' => $this->modified_details( $node_type, $before, $after ),
			);
		}

		$node = 'added' === $kind ? $after : $before;

		return array(
			'summary' => $this->summary( $node_type, $kind, $is_nested ),
			'details' => array( $this->node_description( $node_type, $node, $is_nested ) ),
		);
	}

	/**
	 * @param string $node_type Schema node type.
	 * @param string $kind Change kind.
	 * @param bool   $is_nested Whether the field is below a top-level field.
	 * @return string
	 */
	private function summary( $node_type, $kind, $is_nested ) {
		$label = $this->node_type_label( $node_type, $is_nested );

		return $label . ' ' . $kind . '.';
	}

	/**
	 * @param string $node_type Schema node type.
	 * @param array  $before Earlier node.
	 * @param array  $after Later node.
	 * @return string[]
	 */
	private function modified_details( $node_type, array $before, array $after ) {
		$properties = 'field_group' === $node_type
			? array( 'title' => 'Group title', 'active' => 'Active', 'location' => 'Location rules' )
			: array(
				'name'          => 'Field name',
				'label'         => 'Field label',
				'type'          => 'Field type',
				'required'      => 'Required',
				'instructions'  => 'Instructions',
				'default_value' => 'Default value',
				'conditional_logic' => 'Conditional logic',
			);
		$details    = array();

		foreach ( $properties as $property => $label ) {
			if ( ! array_key_exists( $property, $before ) || ! array_key_exists( $property, $after ) || $before[ $property ] === $after[ $property ] ) {
				continue;
			}

			$details[] = $label . ': ' . $this->format_value( $before[ $property ] ) . ' -> ' . $this->format_value( $after[ $property ] );
		}

		if ( 'field' === $node_type ) {
			$details = array_merge(
				$details,
				$this->setting_details(
					isset( $before['settings'] ) && is_array( $before['settings'] ) ? $before['settings'] : array(),
					isset( $after['settings'] ) && is_array( $after['settings'] ) ? $after['settings'] : array()
				)
			);
			$details = array_merge(
				$details,
				$this->layout_details(
					isset( $before['layouts'] ) && is_array( $before['layouts'] ) ? $before['layouts'] : array(),
					isset( $after['layouts'] ) && is_array( $after['layouts'] ) ? $after['layouts'] : array()
				)
			);
		}

		return $details;
	}

	/**
	 * @param array $before Earlier layouts.
	 * @param array $after Later layouts.
	 * @return string[]
	 */
	private function layout_details( array $before, array $after ) {
		$before = $this->layout_map( $before );
		$after  = $this->layout_map( $after );
		$keys   = array_unique( array_merge( array_keys( $before ), array_keys( $after ) ) );
		sort( $keys, SORT_STRING );
		$details = array();

		foreach ( $keys as $key ) {
			if ( ! isset( $before[ $key ] ) ) {
				$details[] = $this->layout_description( 'added', $after[ $key ] );
				continue;
			}

			if ( ! isset( $after[ $key ] ) ) {
				$details[] = $this->layout_description( 'removed', $before[ $key ] );
				continue;
			}

			$details = array_merge( $details, $this->modified_layout_details( $key, $before[ $key ], $after[ $key ] ) );
		}

		return $details;
	}

	/**
	 * @param string $key Stable layout key.
	 * @param array  $before Earlier layout.
	 * @param array  $after Later layout.
	 * @return string[]
	 */
	private function modified_layout_details( $key, array $before, array $after ) {
		$details   = array();
		$properties = array( 'name' => 'name', 'label' => 'label', 'display' => 'display' );
		$prefix     = 'Layout ' . $this->format_value( $key );

		foreach ( $properties as $property => $label ) {
			$before_value = array_key_exists( $property, $before ) ? $before[ $property ] : null;
			$after_value  = array_key_exists( $property, $after ) ? $after[ $property ] : null;

			if ( $before_value !== $after_value ) {
				$details[] = $prefix . ' ' . $label . ': ' . $this->format_value( $before_value ) . ' -> ' . $this->format_value( $after_value );
			}
		}

		return array_merge(
			$details,
			$this->setting_details(
				isset( $before['settings'] ) && is_array( $before['settings'] ) ? $before['settings'] : array(),
				isset( $after['settings'] ) && is_array( $after['settings'] ) ? $after['settings'] : array(),
				$prefix . ' setting'
			)
		);
	}

	/**
	 * @param string $kind Added or removed.
	 * @param array  $layout Normalized layout.
	 * @return string
	 */
	private function layout_description( $kind, array $layout ) {
		$label = isset( $layout['label'] ) ? $layout['label'] : null;
		$name  = isset( $layout['name'] ) ? $layout['name'] : null;
		$key   = isset( $layout['key'] ) ? $layout['key'] : null;

		return 'Layout ' . $kind . ': ' . $this->format_value( $label ) . ' (' . $this->format_value( $name ) . '), key ' . $this->format_value( $key ) . '.';
	}

	/**
	 * @param array $layouts Candidate normalized layouts.
	 * @return array<string,array>
	 */
	private function layout_map( array $layouts ) {
		$map = array();

		foreach ( $layouts as $layout ) {
			if ( is_array( $layout ) && isset( $layout['key'] ) && is_string( $layout['key'] ) && '' !== $layout['key'] ) {
				$map[ $layout['key'] ] = $layout;
			}
		}

		ksort( $map, SORT_STRING );

		return $map;
	}

	private function setting_details( array $before, array $after, $prefix = 'Setting' ) {
		$keys = array_unique( array_merge( array_keys( $before ), array_keys( $after ) ) );
		sort( $keys, SORT_STRING );
		$details = array();

		foreach ( $keys as $key ) {
			$label = $prefix . ' ' . $this->format_value( (string) $key );
			if ( ! array_key_exists( $key, $before ) ) {
				$details[] = $label . ' added: ' . $this->format_value( $after[ $key ] );
			} elseif ( ! array_key_exists( $key, $after ) ) {
				$details[] = $label . ' removed: ' . $this->format_value( $before[ $key ] );
			} elseif ( $before[ $key ] !== $after[ $key ] ) {
				$details[] = $label . ': ' . $this->format_value( $before[ $key ] ) . ' -> ' . $this->format_value( $after[ $key ] );
			}
		}

		return $details;
	}

	/**
	 * @param string $node_type Schema node type.
	 * @param array  $node Added or removed schema node.
	 * @param bool   $is_nested Whether the field is below a top-level field.
	 * @return string
	 */
	private function node_description( $node_type, array $node, $is_nested ) {
		if ( 'field_group' === $node_type ) {
			return 'Field group: ' . $this->format_value( isset( $node['title'] ) ? $node['title'] : null ) . '.';
		}

		if ( 'field' === $node_type ) {
			$label = isset( $node['label'] ) ? $this->format_value( $node['label'] ) : '(none)';
			$name  = isset( $node['name'] ) ? $this->format_value( $node['name'] ) : '(none)';
			$type  = isset( $node['type'] ) ? $this->format_value( $node['type'] ) : '(none)';

			return $this->node_type_label( $node_type, $is_nested ) . ': ' . $label . ' (' . $name . '), type ' . $type . '.';
		}

		return 'Schema node.';
	}

	/**
	 * @param string $node_type Schema node type.
	 * @param bool   $is_nested Whether the field is below a top-level field.
	 * @return string
	 */
	private function node_type_label( $node_type, $is_nested ) {
		if ( 'field_group' === $node_type ) {
			return 'Field group';
		}

		if ( 'field' === $node_type ) {
			return $is_nested ? 'Nested field' : 'Field';
		}

		return 'Schema node';
	}

	/**
	 * @param mixed $value Schema property value.
	 * @return string
	 */
	private function format_value( $value ) {
		if ( null === $value ) {
			return '(none)';
		}

		if ( is_bool( $value ) ) {
			return $value ? 'yes' : 'no';
		}

		if ( is_string( $value ) ) {
			return '' === $value ? '(empty)' : '"' . $value . '"';
		}

		if ( is_int( $value ) || is_float( $value ) ) {
			return (string) $value;
		}

		$json = json_encode( $value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );

		return false === $json ? '(unavailable)' : $json;
	}
}
