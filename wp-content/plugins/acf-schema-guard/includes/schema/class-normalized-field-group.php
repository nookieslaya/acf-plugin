<?php
/**
 * Normalized ACF field-group value object.
 *
 * @package ACFSchemaGuard
 */

namespace AcfSchemaGuard\Schema;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Represents one canonical ACF field group.
 */
final class NormalizedFieldGroup {
	/** @var string */
	private $key;

	/** @var string */
	private $title;

	/** @var bool */
	private $active;

	/** @var array */
	private $location;

	/** @var NormalizedField[] */
	private $fields;

	/**
	 * @param string            $key Group key.
	 * @param string            $title Group title.
	 * @param bool              $active Whether the group is active.
	 * @param array             $location Location rules.
	 * @param NormalizedField[] $fields Normalized fields.
	 */
	public function __construct( $key, $title, $active, array $location, array $fields ) {
		$this->key      = (string) $key;
		$this->title    = (string) $title;
		$this->active   = (bool) $active;
		$this->location = CanonicalValue::normalize( $location );
		$this->fields   = $this->fields( $fields );
	}

	/**
	 * Gets the stable group key.
	 *
	 * @return string
	 */
	public function key() {
		return $this->key;
	}

	/**
	 * Gets the canonical representation.
	 *
	 * @return array
	 */
	public function to_array() {
		$fields = array();

		foreach ( $this->fields as $field ) {
			$fields[] = $field->to_array();
		}

		return array(
			'key'      => $this->key,
			'title'    => $this->title,
			'active'   => $this->active,
			'location' => $this->location,
			'fields'   => $fields,
		);
	}

	/**
	 * Retains only normalized fields.
	 *
	 * @param array $fields Candidate fields.
	 * @return NormalizedField[]
	 */
	private function fields( array $fields ) {
		$normalized_fields = array();

		foreach ( $fields as $field ) {
			if ( $field instanceof NormalizedField ) {
				$normalized_fields[] = $field;
			}
		}

		return $normalized_fields;
	}
}
