<?php
/**
 * Read-only ACF runtime discovery result.
 *
 * @package ACFSchemaGuard
 */

namespace AcfSchemaGuard\Acf;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Keeps ACF runtime details out of later domain services.
 */
final class AcfEnvironment {
	/** @var bool */
	private $is_available;

	/** @var bool */
	private $is_pro;

	/** @var string|null */
	private $version;

	/** @var string[] */
	private $local_json_paths;

	/** @var FieldGroupDescriptor[] */
	private $field_groups;

	/**
	 * @param bool                   $is_available     Whether required ACF APIs are available.
	 * @param bool                   $is_pro           Whether ACF PRO is available.
	 * @param string|null            $version          Loaded ACF version.
	 * @param string[]               $local_json_paths Configured Local JSON paths.
	 * @param FieldGroupDescriptor[] $field_groups     Discovered field groups.
	 */
	public function __construct( $is_available, $is_pro, $version, array $local_json_paths, array $field_groups ) {
		$this->is_available     = (bool) $is_available;
		$this->is_pro           = (bool) $is_pro;
		$this->version          = null === $version ? null : (string) $version;
		$this->local_json_paths = $this->unique_strings( $local_json_paths );
		$this->field_groups     = $this->field_group_descriptors( $field_groups );
	}

	/** @return bool */
	public function is_available() {
		return $this->is_available;
	}

	/** @return bool */
	public function is_pro() {
		return $this->is_pro;
	}

	/** @return string|null */
	public function version() {
		return $this->version;
	}

	/** @return string[] */
	public function local_json_paths() {
		return $this->local_json_paths;
	}

	/** @return FieldGroupDescriptor[] */
	public function field_groups() {
		return $this->field_groups;
	}

	/**
	 * @param array $values Candidate strings.
	 * @return string[]
	 */
	private function unique_strings( array $values ) {
		$unique = array();

		foreach ( $values as $value ) {
			if ( ! is_string( $value ) || '' === $value || in_array( $value, $unique, true ) ) {
				continue;
			}

			$unique[] = $value;
		}

		return $unique;
	}

	/**
	 * @param array $descriptors Candidate descriptors.
	 * @return FieldGroupDescriptor[]
	 */
	private function field_group_descriptors( array $descriptors ) {
		$valid = array();

		foreach ( $descriptors as $descriptor ) {
			if ( $descriptor instanceof FieldGroupDescriptor ) {
				$valid[] = $descriptor;
			}
		}

		return $valid;
	}
}
