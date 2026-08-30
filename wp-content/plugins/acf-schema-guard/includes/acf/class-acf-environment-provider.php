<?php
/**
 * Read-only adapter around public ACF discovery APIs.
 *
 * @package ACFSchemaGuard
 */

namespace AcfSchemaGuard\Acf;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Converts the available ACF runtime into shallow discovery contracts.
 */
final class AcfEnvironmentProvider {
	/**
	 * Discovers the available ACF runtime without persisting or modifying data.
	 *
	 * @return AcfEnvironment
	 */
	public function discover() {
		if ( ! function_exists( 'acf_get_field_groups' ) ) {
			return new AcfEnvironment( false, false, null, array(), array() );
		}

		$local_json_paths = $this->local_json_paths();
		$local_json_files = $this->local_json_files();
		$field_groups     = acf_get_field_groups();
		$descriptors      = array();

		if ( is_array( $field_groups ) ) {
			foreach ( $field_groups as $field_group ) {
				$descriptor = $this->field_group_descriptor( $field_group, $local_json_files );

				if ( null !== $descriptor ) {
					$descriptors[] = $descriptor;
				}
			}
		}

		return new AcfEnvironment(
			true,
			$this->is_pro(),
			defined( 'ACF_VERSION' ) ? ACF_VERSION : null,
			$local_json_paths,
			$descriptors
		);
	}

	/**
	 * @return bool
	 */
	private function is_pro() {
		return function_exists( 'acf_is_pro' ) && acf_is_pro();
	}

	/**
	 * @return string[]
	 */
	private function local_json_paths() {
		if ( ! function_exists( 'acf_get_setting' ) ) {
			return array();
		}

		$paths = acf_get_setting( 'load_json' );

		return is_array( $paths ) ? $paths : array();
	}

	/**
	 * @return array<string, string>
	 */
	private function local_json_files() {
		if ( ! function_exists( 'acf_get_local_json_files' ) ) {
			return array();
		}

		$files = acf_get_local_json_files();

		return is_array( $files ) ? $files : array();
	}

	/**
	 * @param mixed                $field_group      ACF field-group candidate.
	 * @param array<string, string> $local_json_files Local JSON files by group key.
	 * @return FieldGroupDescriptor|null
	 */
	private function field_group_descriptor( $field_group, array $local_json_files ) {
		if ( ! is_array( $field_group ) || empty( $field_group['key'] ) ) {
			return null;
		}

		$key             = (string) $field_group['key'];
		$local_json_file = isset( $local_json_files[ $key ] ) && is_string( $local_json_files[ $key ] ) ? $local_json_files[ $key ] : null;
		$source          = null === $local_json_file ? FieldGroupDescriptor::SOURCE_RUNTIME : FieldGroupDescriptor::SOURCE_LOCAL_JSON;

		return new FieldGroupDescriptor(
			$key,
			isset( $field_group['title'] ) ? (string) $field_group['title'] : '',
			! empty( $field_group['active'] ),
			$source,
			$local_json_file
		);
	}
}
