<?php
namespace AcfSchemaGuard\Snapshots;

if ( ! defined( 'ABSPATH' ) ) { exit; }

final class AutomaticSnapshotService {
	const SOURCE_ID = 'acf-auto';
	private $repository;

	public function __construct( SnapshotRepository $repository ) {
		$this->repository = $repository;
	}

	public function needs_capture( array $schema ) {
		$latest = $this->repository->latest_for_source( self::SOURCE_ID );

		return null === $latest || $this->hash( $latest->schema() ) !== $this->hash( $schema );
	}

	private function hash( array $schema ) {
		return hash( 'sha256', wp_json_encode( $schema ) );
	}
}
