<?php
/**
 * Immutable normalized ACF field contract.
 *
 * @package ACFSchemaGuard
 */

namespace AcfSchemaGuard\Schema;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Stores semantic field properties without ACF runtime state.
 */
final class NormalizedField {
	/** @var string */
	private $key;

	/** @var string */
	private $name;

	/** @var string */
	private $label;

	/** @var string */
	private $type;

	/** @var bool */
	private $required;

	/** @var string */
	private $instructions;

	/** @var mixed */
	private $default_value;

	/** @var mixed */
	private $conditional_logic;

	/** @var array */
	private $settings;

	/** @var NormalizedField[] */
	private $sub_fields;

	/** @var array */
	private $layouts;

	/**
	 * @param string            $key               ACF field key.
	 * @param string            $name              ACF field name.
	 * @param string            $label             ACF field label.
	 * @param string            $type              ACF field type.
	 * @param bool              $required          Required state.
	 * @param string            $instructions      Field instructions.
	 * @param mixed             $default_value     Default value.
	 * @param mixed             $conditional_logic Conditional logic.
	 * @param array             $settings          Type-specific settings.
	 * @param NormalizedField[] $sub_fields        Nested fields.
	 * @param array             $layouts           Canonical Flexible Content layouts.
	 */
	public function __construct( $key, $name, $label, $type, $required, $instructions, $default_value, $conditional_logic, array $settings, array $sub_fields = array(), array $layouts = array() ) {
		$this->key               = (string) $key;
		$this->name              = (string) $name;
		$this->label             = (string) $label;
		$this->type              = (string) $type;
		$this->required          = (bool) $required;
		$this->instructions      = (string) $instructions;
		$this->default_value     = CanonicalValue::normalize( $default_value );
		$this->conditional_logic = CanonicalValue::normalize( $conditional_logic );
		$this->settings          = CanonicalValue::normalize( $settings );
		$this->sub_fields        = $this->fields( $sub_fields );
		$this->layouts           = CanonicalValue::normalize( $layouts );
	}

	/**
	 * @return string
	 */
	public function key() {
		return $this->key;
	}

	/**
	 * @return array
	 */
	public function to_array() {
		$sub_fields = array();

		foreach ( $this->sub_fields as $field ) {
			$sub_fields[] = $field->to_array();
		}

		return array(
			'key'               => $this->key,
			'name'              => $this->name,
			'label'             => $this->label,
			'type'              => $this->type,
			'required'          => $this->required,
			'instructions'      => $this->instructions,
			'default_value'     => $this->default_value,
			'conditional_logic' => $this->conditional_logic,
			'settings'          => $this->settings,
			'sub_fields'        => $sub_fields,
			'layouts'           => $this->layouts,
		);
	}

	/**
	 * @param array $fields Candidate fields.
	 * @return NormalizedField[]
	 */
	private function fields( array $fields ) {
		$valid = array();

		foreach ( $fields as $field ) {
			if ( $field instanceof NormalizedField ) {
				$valid[] = $field;
			}
		}

		return $valid;
	}
}
