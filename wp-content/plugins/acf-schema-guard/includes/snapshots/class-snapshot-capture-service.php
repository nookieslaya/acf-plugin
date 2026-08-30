<?php
/**
 * Explicit orchestration for normalized schema snapshot capture.
 *
 * @package ACFSchemaGuard
 */

namespace AcfSchemaGuard\Snapshots;

use AcfSchemaGuard\Acf\AcfEnvironmentProvider;
use AcfSchemaGuard\Acf\FullSchemaSource;
use AcfSchemaGuard\Schema\SchemaNormalizer;
use RuntimeException;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Captures the current available ACF schema into immutable storage.
 */
final class SnapshotCaptureService {
	/** @var AcfEnvironmentProvider */
	private $environment_provider;

	/** @var FullSchemaSource */
	private $schema_source;

	/** @var SchemaNormalizer */
	private $schema_normalizer;

	/** @var SnapshotRepository */
	private $snapshot_repository;

	/**
	 * @param AcfEnvironmentProvider $environment_provider ACF environment provider.
	 * @param FullSchemaSource        $schema_source Full schema source.
	 * @param SchemaNormalizer        $schema_normalizer Schema normalizer.
	 * @param SnapshotRepository      $snapshot_repository Snapshot storage.
	 */
	public function __construct( AcfEnvironmentProvider $environment_provider, FullSchemaSource $schema_source, SchemaNormalizer $schema_normalizer, SnapshotRepository $snapshot_repository ) {
		$this->environment_provider = $environment_provider;
		$this->schema_source        = $schema_source;
		$this->schema_normalizer    = $schema_normalizer;
		$this->snapshot_repository  = $snapshot_repository;
	}

	/**
	 * Captures the current ACF schema for one caller-selected source.
	 *
	 * @param string $source_id Snapshot source identifier.
	 * @return SchemaSnapshot
	 */
	public function capture( $source_id ) {
		if ( ! $this->environment_provider->discover()->is_available() ) {
			throw new RuntimeException( 'Cannot capture a snapshot because ACF is unavailable.' );
		}

		$schema = $this->schema_normalizer->normalize( $this->schema_source->field_groups() );
		$snapshot = new SchemaSnapshot(
			wp_generate_uuid4(),
			$source_id,
			$schema->to_array(),
			gmdate( 'Y-m-d H:i:s' )
		);

		$this->snapshot_repository->insert( $snapshot );

		return $snapshot;
	}
}
