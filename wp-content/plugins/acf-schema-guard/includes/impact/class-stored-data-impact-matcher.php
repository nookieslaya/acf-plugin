<?php
/**
 * Immutable, schema-derived post-meta matcher for one affected ACF field.
 *
 * @package ACFSchemaGuard
 */

namespace AcfSchemaGuard\Impact;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class StoredDataImpactMatcher {
	private $field_name;
	private $field_path;
	private $pattern;
	private $query_type;
	private $query_value;
	private $confidence;

	public function __construct( $field_name, array $field_path, $pattern, $query_type, $query_value, $confidence ) {
		$this->field_name  = (string) $field_name;
		$this->field_path  = array_values( $field_path );
		$this->pattern     = (string) $pattern;
		$this->query_type  = (string) $query_type;
		$this->query_value = (string) $query_value;
		$this->confidence  = (string) $confidence;
	}

	public function field_name() {
		return $this->field_name;
	}

	public function query_type() {
		return $this->query_type;
	}

	public function query_value() {
		return $this->query_value;
	}

	public function to_array() {
		return array(
			'field_name' => $this->field_name,
			'field_path' => $this->field_path,
			'pattern'     => $this->pattern,
			'query_type'  => $this->query_type,
			'confidence'  => $this->confidence,
		);
	}
}
