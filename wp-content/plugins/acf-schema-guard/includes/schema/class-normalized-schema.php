<?php
/**
 * Normalized ACF schema value object.
 *
 * @package ACFSchemaGuard
 */

namespace AcfSchemaGuard\Schema;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Represents a versioned canonical ACF schema.
 */
final class NormalizedSchema {
	/** @var int */
	const VERSION = 1;

	/** @var NormalizedFieldGroup[] */
	private $field_groups;

	/**
	 * @param NormalizedFieldGroup[] $field_groups Normalized field groups.
	 */
	public function __construct( array $field_groups ) {
		$this->field_groups = $this->field_groups( $field_groups );

		usort(
			$this->field_groups,
			function ( NormalizedFieldGroup $left, NormalizedFieldGroup $right ) {
				return strcmp( $left->key(), $right->key() );
			}
		);
	}

	/**
	 * Gets the canonical representation.
	 *
	 * @return array
	 */
	public function to_array() {
		$field_groups = array();

		foreach ( $this->field_groups as $field_group ) {
			$field_groups[] = $field_group->to_array();
		}

		return array(
			'schema_version' => self::VERSION,
			'field_groups'   => $field_groups,
		);
	}

	/**
	 * Retains only normalized field groups.
	 *
	 * @param array $field_groups Candidate field groups.
	 * @return NormalizedFieldGroup[]
	 */
	private function field_groups( array $field_groups ) {
		$normalized_groups = array();

		foreach ( $field_groups as $field_group ) {
			if ( $field_group instanceof NormalizedFieldGroup ) {
				$normalized_groups[] = $field_group;
			}
		}

		return $normalized_groups;
	}
}
