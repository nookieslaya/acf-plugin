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
				'summary' => $this->summary( $node_type, $kind ),
				'details' => $this->modified_details( $node_type, $before, $after ),
			);
		}

		$node = 'added' === $kind ? $after : $before;

		return array(
			'summary' => $this->summary( $node_type, $kind ),
			'details' => array( $this->node_description( $node_type, $node ) ),
		);
	}

	/**
	 * @param string $node_type Schema node type.
	 * @param string $kind Change kind.
	 * @return string
	 */
	private function summary( $node_type, $kind ) {
		$label = 'field_group' === $node_type ? 'Field group' : ( 'field' === $node_type ? 'Field' : 'Schema node' );

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
			? array( 'title' => 'Group title', 'active' => 'Active' )
			: array(
				'name'          => 'Field name',
				'label'         => 'Field label',
				'type'          => 'Field type',
				'required'      => 'Required',
				'instructions'  => 'Instructions',
				'default_value' => 'Default value',
			);
		$details    = array();

		foreach ( $properties as $property => $label ) {
			if ( ! array_key_exists( $property, $before ) || ! array_key_exists( $property, $after ) || $before[ $property ] === $after[ $property ] ) {
				continue;
			}

			$details[] = $label . ': ' . $this->format_value( $before[ $property ] ) . ' -> ' . $this->format_value( $after[ $property ] );
		}

		return $details;
	}

	/**
	 * @param string $node_type Schema node type.
	 * @param array  $node Added or removed schema node.
	 * @return string
	 */
	private function node_description( $node_type, array $node ) {
		if ( 'field_group' === $node_type ) {
			return 'Field group: ' . $this->format_value( isset( $node['title'] ) ? $node['title'] : null ) . '.';
		}

		if ( 'field' === $node_type ) {
			$label = isset( $node['label'] ) ? $this->format_value( $node['label'] ) : '(none)';
			$name  = isset( $node['name'] ) ? $this->format_value( $node['name'] ) : '(none)';
			$type  = isset( $node['type'] ) ? $this->format_value( $node['type'] ) : '(none)';

			return 'Field: ' . $label . ' (' . $name . '), type ' . $type . '.';
		}

		return 'Schema node.';
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
