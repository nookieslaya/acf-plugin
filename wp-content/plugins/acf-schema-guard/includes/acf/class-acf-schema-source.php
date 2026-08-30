<?php
/**
 * ACF implementation of the complete schema source boundary.
 *
 * @package ACFSchemaGuard
 */

namespace AcfSchemaGuard\Acf;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Reads complete field-group definitions through public ACF APIs.
 */
final class AcfSchemaSource implements FullSchemaSource {
	/**
	 * Gets field groups and their fields without changing the ACF runtime.
	 *
	 * @return array[] Each group includes its fields.
	 */
	public function field_groups() {
		if ( ! function_exists( 'acf_get_field_groups' ) || ! function_exists( 'acf_get_fields' ) ) {
			return array();
		}

		$field_groups = acf_get_field_groups();

		if ( ! is_array( $field_groups ) ) {
			return array();
		}

		$complete_groups = array();

		foreach ( $field_groups as $field_group ) {
			if ( ! is_array( $field_group ) || empty( $field_group['key'] ) ) {
				continue;
			}

			$fields = acf_get_fields( $field_group );

			$field_group['fields'] = is_array( $fields ) ? $fields : array();
			$complete_groups[]     = $field_group;
		}

		return $complete_groups;
	}
}
