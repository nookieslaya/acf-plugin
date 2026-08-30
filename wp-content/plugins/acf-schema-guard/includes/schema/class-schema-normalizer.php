<?php
/**
 * Converts raw ACF definitions into normalized schema contracts.
 *
 * @package ACFSchemaGuard
 */

namespace AcfSchemaGuard\Schema;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Produces deterministic schema contracts without inspecting ACF internals.
 */
final class SchemaNormalizer {
	/**
	 * Normalizes full ACF field-group arrays.
	 *
	 * @param array $field_groups Raw ACF field groups.
	 * @return NormalizedSchema
	 */
	public function normalize( array $field_groups ) {
		$normalized_groups = array();

		foreach ( $field_groups as $field_group ) {
			if ( is_array( $field_group ) && ! empty( $field_group['key'] ) ) {
				$normalized_groups[] = $this->normalize_field_group( $field_group );
			}
		}

		return new NormalizedSchema( $normalized_groups );
	}

	/**
	 * @param array $field_group Raw ACF field group.
	 * @return NormalizedFieldGroup
	 */
	private function normalize_field_group( array $field_group ) {
		$fields = $this->normalize_fields( $this->array_value( $field_group, 'fields' ) );

		usort(
			$fields,
			function ( NormalizedField $left, NormalizedField $right ) {
				return strcmp( $left->key(), $right->key() );
			}
		);

		return new NormalizedFieldGroup(
			(string) $field_group['key'],
			$this->string_value( $field_group, 'title' ),
			! empty( $field_group['active'] ),
			$this->normalize_rule_groups( $this->array_value( $field_group, 'location' ) ),
			$fields
		);
	}

	/**
	 * @param array $field Raw ACF field.
	 * @return NormalizedField
	 */
	private function normalize_field( array $field ) {
		return new NormalizedField(
			$this->string_value( $field, 'key' ),
			$this->string_value( $field, 'name' ),
			$this->string_value( $field, 'label' ),
			$this->string_value( $field, 'type' ),
			! empty( $field['required'] ),
			$this->string_value( $field, 'instructions' ),
			isset( $field['default_value'] ) ? $field['default_value'] : null,
			$this->normalize_rule_groups( $this->array_value( $field, 'conditional_logic' ) ),
			$this->settings( $field, array( 'key', 'name', 'label', 'type', 'required', 'instructions', 'default_value', 'conditional_logic', 'sub_fields', 'layouts' ) ),
			$this->normalize_fields( $this->array_value( $field, 'sub_fields' ) ),
			$this->normalize_layouts( $this->array_value( $field, 'layouts' ) )
		);
	}

	/**
	 * @param array $fields Raw ACF fields.
	 * @return NormalizedField[]
	 */
	private function normalize_fields( array $fields ) {
		$normalized_fields = array();

		foreach ( $fields as $field ) {
			if ( is_array( $field ) && ! empty( $field['key'] ) ) {
				$normalized_fields[] = $this->normalize_field( $field );
			}
		}

		return $normalized_fields;
	}

	/**
	 * @param array $layouts Raw Flexible Content layouts.
	 * @return array[]
	 */
	private function normalize_layouts( array $layouts ) {
		$normalized_layouts = array();

		foreach ( $layouts as $layout ) {
			if ( ! is_array( $layout ) || empty( $layout['key'] ) ) {
				continue;
			}

			$sub_fields = array();

			foreach ( $this->normalize_fields( $this->array_value( $layout, 'sub_fields' ) ) as $field ) {
				$sub_fields[] = $field->to_array();
			}

			$normalized_layouts[] = array(
				'key'        => (string) $layout['key'],
				'name'       => $this->string_value( $layout, 'name' ),
				'label'      => $this->string_value( $layout, 'label' ),
				'display'    => $this->string_value( $layout, 'display' ),
				'settings'   => $this->settings( $layout, array( 'key', 'name', 'label', 'display', 'sub_fields' ) ),
				'sub_fields' => $sub_fields,
			);
		}

		usort(
			$normalized_layouts,
			function ( array $left, array $right ) {
				return strcmp( $left['key'], $right['key'] );
			}
		);

		return $normalized_layouts;
	}

	/**
	 * Canonicalizes ACF OR-of-AND rule groups.
	 *
	 * @param array $rule_groups ACF location or conditional rules.
	 * @return array
	 */
	private function normalize_rule_groups( array $rule_groups ) {
		$normalized_groups = array();

		foreach ( $rule_groups as $rules ) {
			if ( ! is_array( $rules ) ) {
				continue;
			}

			$normalized_rules = array();

			foreach ( $rules as $rule ) {
				if ( is_array( $rule ) ) {
					$normalized_rules[] = CanonicalValue::normalize( $rule );
				}
			}

			usort( $normalized_rules, array( $this, 'compare_rule' ) );
			$normalized_groups[] = $normalized_rules;
		}

		usort( $normalized_groups, array( $this, 'compare_values' ) );

		return $normalized_groups;
	}

	/**
	 * @param array $source Raw ACF configuration.
	 * @param array $structural_keys Keys represented by the normalized contract.
	 * @return array
	 */
	private function settings( array $source, array $structural_keys ) {
		$settings = array();
		$excluded = array_merge( $structural_keys, array( 'ID', 'id', 'parent', 'parent_layout', 'prefix', 'value', 'menu_order' ) );

		foreach ( $source as $key => $value ) {
			if ( in_array( $key, $excluded, true ) ) {
				continue;
			}

			$settings[ $key ] = $value;
		}

		return CanonicalValue::normalize( $settings );
	}

	/**
	 * @param array  $left First rule.
	 * @param array  $right Second rule.
	 * @return int
	 */
	private function compare_rule( array $left, array $right ) {
		return strcmp( $this->rule_sort_key( $left ), $this->rule_sort_key( $right ) );
	}

	/**
	 * @param mixed $left First value.
	 * @param mixed $right Second value.
	 * @return int
	 */
	private function compare_values( $left, $right ) {
		return strcmp( serialize( $left ), serialize( $right ) );
	}

	/**
	 * @param array $rule ACF rule.
	 * @return string
	 */
	private function rule_sort_key( array $rule ) {
		return $this->string_value( $rule, 'param' ) . "\0" . $this->string_value( $rule, 'operator' ) . "\0" . $this->string_value( $rule, 'value' );
	}

	/**
	 * @param array  $source Source array.
	 * @param string $key Array key.
	 * @return array
	 */
	private function array_value( array $source, $key ) {
		return isset( $source[ $key ] ) && is_array( $source[ $key ] ) ? $source[ $key ] : array();
	}

	/**
	 * @param array  $source Source array.
	 * @param string $key Array key.
	 * @return string
	 */
	private function string_value( array $source, $key ) {
		return isset( $source[ $key ] ) ? (string) $source[ $key ] : '';
	}
}
