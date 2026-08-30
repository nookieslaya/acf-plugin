<?php
/**
 * Immutable persisted normalized-schema snapshot.
 *
 * @package ACFSchemaGuard
 */

namespace AcfSchemaGuard\Snapshots;

use DateTimeImmutable;
use DateTimeZone;
use RuntimeException;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Represents one immutable normalized-schema snapshot.
 */
final class SchemaSnapshot {
	/** @var int */
	const SCHEMA_VERSION = 1;

	/** @var string */
	private $id;

	/** @var string */
	private $source_id;

	/** @var array */
	private $schema;

	/** @var string */
	private $created_at;

	/**
	 * @param string $id Snapshot UUID.
	 * @param string $source_id Source identifier.
	 * @param array  $schema Canonical normalized schema.
	 * @param string $created_at UTC creation time.
	 */
	public function __construct( $id, $source_id, array $schema, $created_at ) {
		$this->id         = $this->validated_id( $id );
		$this->source_id  = $this->validated_source_id( $source_id );
		$this->schema     = $this->validated_schema( $schema );
		$this->created_at = $this->validated_created_at( $created_at );
	}

	/** @return string */
	public function id() {
		return $this->id;
	}

	/** @return string */
	public function source_id() {
		return $this->source_id;
	}

	/** @return array */
	public function schema() {
		return $this->schema;
	}

	/** @return string */
	public function created_at() {
		return $this->created_at;
	}

	/**
	 * Gets the storage row using canonical JSON.
	 *
	 * @return array
	 */
	public function to_row() {
		$schema_json = json_encode( $this->schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );

		if ( false === $schema_json ) {
			throw new RuntimeException( 'Snapshot schema cannot be encoded as JSON.' );
		}

		return array(
			'id'             => $this->id,
			'source_id'      => $this->source_id,
			'schema_version' => self::SCHEMA_VERSION,
			'schema'         => $schema_json,
			'created_at'     => $this->created_at,
		);
	}

	/**
	 * Reconstructs a snapshot from one database row.
	 *
	 * @param array $row Stored snapshot row.
	 * @return SchemaSnapshot
	 */
	public static function from_row( array $row ) {
		if ( ! isset( $row['schema_version'] ) || self::SCHEMA_VERSION !== (int) $row['schema_version'] ) {
			throw new RuntimeException( 'Snapshot schema version is unsupported.' );
		}

		$schema_json = isset( $row['schema'] ) && is_string( $row['schema'] ) ? $row['schema'] : '';
		$schema      = json_decode( $schema_json, true );

		if ( ! is_array( $schema ) || JSON_ERROR_NONE !== json_last_error() ) {
			throw new RuntimeException( 'Snapshot schema JSON is malformed.' );
		}

		return new self(
			isset( $row['id'] ) ? $row['id'] : '',
			isset( $row['source_id'] ) ? $row['source_id'] : '',
			$schema,
			isset( $row['created_at'] ) ? $row['created_at'] : ''
		);
	}

	/** @return string */
	private function validated_id( $id ) {
		$id = (string) $id;

		if ( 1 !== preg_match( '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $id ) ) {
			throw new RuntimeException( 'Snapshot ID must be a lowercase UUID.' );
		}

		return $id;
	}

	/** @return string */
	private function validated_source_id( $source_id ) {
		$source_id = (string) $source_id;

		if ( '' === trim( $source_id ) || 191 < strlen( $source_id ) ) {
			throw new RuntimeException( 'Snapshot source ID must contain at most 191 bytes.' );
		}

		return $source_id;
	}

	/** @return array */
	private function validated_schema( array $schema ) {
		if ( ! isset( $schema['schema_version'], $schema['field_groups'] ) || self::SCHEMA_VERSION !== (int) $schema['schema_version'] || ! is_array( $schema['field_groups'] ) ) {
			throw new RuntimeException( 'Snapshot schema must use the supported normalized schema version.' );
		}

		return $schema;
	}

	/** @return string */
	private function validated_created_at( $created_at ) {
		$created_at = (string) $created_at;
		$timezone   = new DateTimeZone( 'UTC' );
		$date       = DateTimeImmutable::createFromFormat( '!Y-m-d H:i:s', $created_at, $timezone );
		$errors     = DateTimeImmutable::getLastErrors();

		if ( false === $date || ( is_array( $errors ) && ( 0 < $errors['warning_count'] || 0 < $errors['error_count'] ) ) || $created_at !== $date->format( 'Y-m-d H:i:s' ) ) {
			throw new RuntimeException( 'Snapshot creation time must be a UTC Y-m-d H:i:s value.' );
		}

		return $created_at;
	}
}
