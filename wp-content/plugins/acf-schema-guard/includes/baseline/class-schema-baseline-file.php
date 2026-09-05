<?php
/**
 * Reads and writes versioned, Git-friendly schema baseline files.
 *
 * @package ACFSchemaGuard
 */

namespace AcfSchemaGuard\Baseline;

use RuntimeException;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Represents the portable baseline-file format.
 */
final class SchemaBaselineFile {
	/** @var int */
	const FORMAT_VERSION = 1;

	/**
	 * Encodes one normalized schema as a portable baseline document.
	 *
	 * @param array $schema Normalized schema.
	 * @return string
	 */
	public function encode( array $schema ) {
		$document = $this->document( $schema );
		$json     = json_encode( $document, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );

		if ( false === $json ) {
			throw new RuntimeException( 'Baseline file could not be encoded as JSON.' );
		}

		return $json . PHP_EOL;
	}

	/**
	 * Writes a baseline to an explicitly supplied, non-existing file path.
	 *
	 * @param string $path Destination path.
	 * @param array  $schema Normalized schema.
	 * @param bool   $overwrite Whether an explicit caller opted into replacement.
	 * @return void
	 */
	public function write( $path, array $schema, $overwrite = false ) {
		$path = $this->validated_path( $path );

		if ( file_exists( $path ) && ! $overwrite ) {
			throw new RuntimeException( 'Baseline file already exists. Re-run with --force to replace it.' );
		}

		if ( false === file_put_contents( $path, $this->encode( $schema ), LOCK_EX ) ) {
			throw new RuntimeException( 'Baseline file could not be written: ' . $path );
		}
	}

	/**
	 * Loads a validated normalized schema from a baseline file.
	 *
	 * @param string $path Baseline path.
	 * @return array
	 */
	public function read( $path ) {
		$path = $this->validated_path( $path );

		if ( ! is_file( $path ) || ! is_readable( $path ) ) {
			throw new RuntimeException( 'Baseline file is not readable: ' . $path );
		}

		$json = file_get_contents( $path );
		if ( false === $json ) {
			throw new RuntimeException( 'Baseline file could not be read: ' . $path );
		}

		$document = json_decode( $json, true );
		if ( ! is_array( $document ) || JSON_ERROR_NONE !== json_last_error() ) {
			throw new RuntimeException( 'Baseline file contains malformed JSON.' );
		}

		if ( ! isset( $document['format_version'] ) || self::FORMAT_VERSION !== (int) $document['format_version'] ) {
			throw new RuntimeException( 'Baseline file format version is unsupported.' );
		}

		if ( ! isset( $document['schema_version'], $document['schema'] ) || ! is_array( $document['schema'] ) ) {
			throw new RuntimeException( 'Baseline file does not contain a valid normalized schema.' );
		}

		if ( (int) $document['schema_version'] !== (int) $document['schema']['schema_version'] || ! isset( $document['schema']['field_groups'] ) || ! is_array( $document['schema']['field_groups'] ) ) {
			throw new RuntimeException( 'Baseline file does not contain a valid normalized schema.' );
		}

		return $document['schema'];
	}

	/**
	 * Builds and validates the portable baseline document.
	 *
	 * @param array $schema Normalized schema.
	 * @return array
	 */
	private function document( array $schema ) {
		if ( ! isset( $schema['schema_version'], $schema['field_groups'] ) || ! is_array( $schema['field_groups'] ) ) {
			throw new RuntimeException( 'Baseline schema is not a supported normalized schema.' );
		}

		return array(
			'format_version' => self::FORMAT_VERSION,
			'schema_version' => (int) $schema['schema_version'],
			'schema'         => $schema,
		);
	}

	/**
	 * @param string $path Candidate path.
	 * @return string
	 */
	private function validated_path( $path ) {
		$path = trim( (string) $path );

		if ( '' === $path ) {
			throw new RuntimeException( 'Provide a baseline file path.' );
		}

		return $path;
	}
}
