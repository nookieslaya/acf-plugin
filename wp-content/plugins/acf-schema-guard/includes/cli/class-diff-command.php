<?php
/**
 * Holds dependencies for the read-only WP-CLI snapshot diff command.
 *
 * @package ACFSchemaGuard
 */

namespace AcfSchemaGuard\Cli;

use AcfSchemaGuard\Diff\SnapshotAnalysisService;
use AcfSchemaGuard\Snapshots\SnapshotRepository;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class DiffCommand {
	/** @var SnapshotRepository */
	private $snapshots;

	/** @var SnapshotAnalysisService */
	private $analysis_service;

	/**
	 * @param SnapshotRepository      $snapshots Snapshot repository.
	 * @param SnapshotAnalysisService $analysis_service Analysis service.
	 */
	public function __construct( SnapshotRepository $snapshots, SnapshotAnalysisService $analysis_service ) {
		$this->snapshots        = $snapshots;
		$this->analysis_service = $analysis_service;
	}

	/**
	 * Compares two persisted snapshots without modifying them.
	 *
	 * @param string[]            $args Snapshot IDs.
	 * @param array<string,mixed> $assoc_args Command options.
	 * @return void
	 */
	public function diff( $args, $assoc_args ) {
		if ( 2 !== count( $args ) ) {
			\WP_CLI::error( 'Provide exactly two snapshot IDs: before-id and after-id.' );
		}

		$before = $this->snapshots->find( $args[0] );
		$after  = $this->snapshots->find( $args[1] );

		if ( null === $before || null === $after ) {
			\WP_CLI::error( 'One or both snapshots could not be found.' );
		}

		$analysis = $this->analysis_service->analyze( $before, $after )->to_array();
		$format   = $this->output_format( $assoc_args );

		if ( 'json' === $format ) {
			$json = json_encode( $analysis, JSON_PRETTY_PRINT );
			if ( false === $json ) {
				\WP_CLI::error( 'Could not encode diff results as JSON.' );
			}
			\WP_CLI::line( $json );
			return;
		}

		$formatter = new FindingOutputFormatter();
		$items     = $formatter->table_items( $analysis['findings'] );
		if ( empty( $items ) ) {
			\WP_CLI::success( 'No schema changes found.' );
			return;
		}

		\WP_CLI\Utils\format_items( 'table', $items, $formatter->table_fields() );
	}

	private function output_format( $assoc_args ) {
		$format = isset( $assoc_args['format'] ) ? strtolower( (string) $assoc_args['format'] ) : 'table';
		if ( ! in_array( $format, array( 'table', 'json' ), true ) ) {
			\WP_CLI::error( sprintf( 'Unsupported format: %s. Use table or json.', $format ) );
		}
		return $format;
	}

}
