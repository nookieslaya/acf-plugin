<?php
/**
 * Reads ACF field groups independently from the database and Local JSON.
 *
 * @package ACFSchemaGuard
 */

namespace AcfSchemaGuard\Acf;

use AcfSchemaGuard\Schema\SchemaNormalizer;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class AcfSourceHealthProvider {
	/** @var SourceHealthAnalyzer */
	private $analyzer;

	/** @var SchemaNormalizer */
	private $normalizer;

	/**
	 * @param SourceHealthAnalyzer|null $analyzer   Source comparison service.
	 * @param SchemaNormalizer|null     $normalizer Raw group normalizer.
	 */
	public function __construct( $analyzer = null, $normalizer = null ) {
		$this->analyzer   = $analyzer instanceof SourceHealthAnalyzer ? $analyzer : new SourceHealthAnalyzer();
		$this->normalizer = $normalizer instanceof SchemaNormalizer ? $normalizer : new SchemaNormalizer();
	}

	/**
	 * Discovers current source health without changing ACF or WordPress state.
	 *
	 * @return SourceHealthReport
	 */
	public function discover() {
		if ( ! $this->is_available() ) {
			return new SourceHealthReport( false, array() );
		}

		return $this->analyzer->analyze( $this->database_groups(), $this->json_groups() );
	}

	/** @return bool */
	private function is_available() {
		return function_exists( 'acf_get_field_group' ) && function_exists( 'acf_get_raw_fields' ) && function_exists( 'acf_get_setting' ) && function_exists( 'get_posts' );
	}

	/**
	 * Loads field groups by post ID so Local JSON runtime overrides do not mask
	 * the database representation.
	 *
	 * @return array[]
	 */
	private function database_groups() {
		$posts  = get_posts(
			array(
				'post_type'      => 'acf-field-group',
				'post_status'    => 'any',
				'posts_per_page' => -1,
				'orderby'        => 'ID',
				'order'          => 'ASC',
			)
		);
		$groups = array();

		if ( ! is_array( $posts ) ) {
			return $groups;
		}

		foreach ( $posts as $post ) {
			if ( ! is_object( $post ) || empty( $post->ID ) ) {
				continue;
			}

			$group = acf_get_field_group( $post->ID );

			if ( ! is_array( $group ) || empty( $group['key'] ) ) {
				continue;
			}

			if ( FieldGroupDescriptor::is_trashed_key( $group['key'] ) ) {
				continue;
			}

			$group['fields'] = $this->database_fields( $post->ID );
			$group['_source_modified'] = isset( $post->post_modified_gmt ) ? strtotime( $post->post_modified_gmt ) : null;
			$groups[]        = $this->normalize_group( $group );
		}

		return array_filter( $groups );
	}

	/**
	 * Reads raw database fields without allowing Local JSON to override them.
	 *
	 * @param int $parent_id ACF field group or field post ID.
	 * @return array[]
	 */
	private function database_fields( $parent_id ) {
		$fields = acf_get_raw_fields( (int) $parent_id );

		if ( ! is_array( $fields ) ) {
			return array();
		}

		foreach ( $fields as &$field ) {
			if ( ! is_array( $field ) || empty( $field['ID'] ) ) {
				continue;
			}

			$field['sub_fields'] = $this->database_fields( $field['ID'] );

			if ( 'flexible_content' === $field['type'] ) {
				$field['layouts']    = $this->database_layouts( $field, $field['sub_fields'] );
				$field['sub_fields'] = array();
			}
		}
		unset( $field );

		return $fields;
	}

	/**
	 * @param array   $field      Raw Flexible Content field.
	 * @param array[] $sub_fields Raw fields directly below the Flexible Content field.
	 * @return array[]
	 */
	private function database_layouts( array $field, array $sub_fields ) {
		$layouts = isset( $field['layouts'] ) && is_array( $field['layouts'] ) ? $field['layouts'] : array();

		foreach ( $layouts as &$layout ) {
			if ( ! is_array( $layout ) || empty( $layout['key'] ) ) {
				continue;
			}

			$layout['sub_fields'] = array_values(
				array_filter(
					$sub_fields,
					static function ( $sub_field ) use ( $layout ) {
						return is_array( $sub_field ) && isset( $sub_field['parent_layout'] ) && $layout['key'] === $sub_field['parent_layout'];
					}
				)
			);
		}
		unset( $layout );

		return array_values( $layouts );
	}

	/**
	 * @return array[]
	 */
	private function json_groups() {
		$groups = array();

		foreach ( $this->json_paths() as $path ) {
			foreach ( $this->json_files( $path ) as $file ) {
				$group = $this->read_json_group( $file );

				if ( null !== $group ) {
					$groups[] = $this->normalize_group( $group );
				}
			}
		}

		return array_filter( $groups );
	}

	/** @return string[] */
	private function json_paths() {
		$paths = acf_get_setting( 'load_json' );

		return is_array( $paths ) ? $paths : array();
	}

	/**
	 * @param string $path Configured ACF Local JSON path.
	 * @return string[]
	 */
	private function json_files( $path ) {
		if ( ! is_string( $path ) || '' === $path || ! is_dir( $path ) ) {
			return array();
		}

		$files = glob( rtrim( $path, '/\\' ) . DIRECTORY_SEPARATOR . '*.json' );

		return is_array( $files ) ? $files : array();
	}

	/**
	 * @param string $file Local JSON file path.
	 * @return array|null
	 */
	private function read_json_group( $file ) {
		$json = is_string( $file ) ? file_get_contents( $file ) : false;

		if ( false === $json ) {
			return null;
		}

		$group = json_decode( $json, true );

		if ( ! is_array( $group ) || JSON_ERROR_NONE !== json_last_error() || empty( $group['key'] ) || 0 !== strpos( (string) $group['key'], 'group_' ) ) {
			return null;
		}

		if ( FieldGroupDescriptor::is_trashed_key( $group['key'] ) ) {
			return null;
		}

		$group['_source_modified'] = isset( $group['modified'] ) ? (int) $group['modified'] : null;

		return $group;
	}

	/**
	 * @param array $group Raw ACF field group.
	 * @return array|null
	 */
	private function normalize_group( array $group ) {
		$schema = $this->normalizer->normalize( array( $group ) )->to_array();

		if ( ! isset( $schema['field_groups'][0] ) ) {
			return null;
		}

		$normalized = $schema['field_groups'][0];
		if ( isset( $group['_source_modified'] ) ) { $normalized['_source_modified'] = (int) $group['_source_modified']; }
		return $normalized;
	}
}
