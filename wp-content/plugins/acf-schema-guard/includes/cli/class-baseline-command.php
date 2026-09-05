<?php
/**
 * Provides portable baseline export and CI-safe schema checks for WP-CLI.
 *
 * @package ACFSchemaGuard
 */

namespace AcfSchemaGuard\Cli;

use AcfSchemaGuard\Baseline\SchemaBaselineFile;
use AcfSchemaGuard\Diff\SnapshotAnalysisService;
use RuntimeException;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Compares the effective current schema with a Git-versioned baseline file.
 */
final class BaselineCommand {
	/** @var SchemaBaselineFile */
	private $baseline_file;

	/** @var SnapshotAnalysisService */
	private $analysis_service;

	/** @var callable */
	private $current_schema_callback;

	/**
	 * @param SchemaBaselineFile      $baseline_file Baseline reader and writer.
	 * @param SnapshotAnalysisService $analysis_service Schema analysis service.
	 * @param callable                $current_schema_callback Gets the effective normalized schema.
	 */
	public function __construct( SchemaBaselineFile $baseline_file, SnapshotAnalysisService $analysis_service, $current_schema_callback ) {
		$this->baseline_file           = $baseline_file;
		$this->analysis_service        = $analysis_service;
		$this->current_schema_callback = $current_schema_callback;
	}

	/**
	 * Exports the effective current schema to a portable baseline file.
	 *
	 * @param string[]            $args Command arguments.
	 * @param array<string,mixed> $assoc_args Command options.
	 * @return void
	 */
	public function export( $args, $assoc_args ) {
		$path = $this->path_argument( $args );

		try {
			$this->baseline_file->write( $path, $this->current_schema(), isset( $assoc_args['force'] ) );
		} catch ( RuntimeException $exception ) {
			\WP_CLI::error( $exception->getMessage() );
		}

		\WP_CLI::success( 'Schema baseline exported: ' . $path );
	}

	/**
	 * Compares a portable baseline file with the effective current schema.
	 *
	 * @param string[]            $args Command arguments.
	 * @param array<string,mixed> $assoc_args Command options.
	 * @return void
	 */
	public function check( $args, $assoc_args ) {
		$path = $this->path_argument( $args );

		try {
			$analysis = $this->analysis_service->analyze_schemas( $this->baseline_file->read( $path ), $this->current_schema() )->to_array();
		} catch ( RuntimeException $exception ) {
			\WP_CLI::error( $exception->getMessage() );
		}

		$format = $this->output_format( $assoc_args );
		if ( 'json' === $format ) {
			$json = json_encode( $analysis, JSON_PRETTY_PRINT );
			if ( false === $json ) {
				\WP_CLI::error( 'Could not encode baseline check results as JSON.' );
			}
			\WP_CLI::line( $json );
		} else {
			$this->table( $analysis['findings'] );
		}

		if ( isset( $assoc_args['fail-on-breaking'] ) && $this->has_breaking( $analysis['findings'] ) ) {
			\WP_CLI::error( 'Breaking schema changes found.' );
		}
	}

	/**
	 * @param string[] $args Command arguments.
	 * @return string
	 */
	private function path_argument( $args ) {
		if ( 1 !== count( $args ) ) {
			\WP_CLI::error( 'Provide exactly one baseline file path.' );
		}

		return $args[0];
	}

	/**
	 * @return array
	 */
	private function current_schema() {
		$schema = call_user_func( $this->current_schema_callback );

		if ( ! is_array( $schema ) ) {
			throw new RuntimeException( 'Current ACF schema could not be normalized.' );
		}

		return $schema;
	}

	/**
	 * @param array<string,mixed> $assoc_args Command options.
	 * @return string
	 */
	private function output_format( $assoc_args ) {
		$format = isset( $assoc_args['format'] ) ? strtolower( (string) $assoc_args['format'] ) : 'table';
		if ( ! in_array( $format, array( 'table', 'json' ), true ) ) {
			\WP_CLI::error( sprintf( 'Unsupported format: %s. Use table or json.', $format ) );
		}

		return $format;
	}

	/**
	 * @param array[] $findings Classified findings.
	 * @return void
	 */
	private function table( $findings ) {
		if ( empty( $findings ) ) {
			\WP_CLI::success( 'No schema changes found.' );
			return;
		}

		$items = array();
		foreach ( $findings as $finding ) {
			$change = $finding['change'];
			$items[] = array(
				'kind'      => $change['kind'],
				'node_type' => $change['node_type'],
				'path'      => implode( '.', $change['path'] ),
				'severity'  => $finding['severity'],
				'rationale' => $finding['rationale'],
			);
		}

		\WP_CLI\Utils\format_items( 'table', $items, array( 'kind', 'node_type', 'path', 'severity', 'rationale' ) );
	}

	/**
	 * @param array[] $findings Classified findings.
	 * @return bool
	 */
	private function has_breaking( $findings ) {
		foreach ( $findings as $finding ) {
			if ( in_array( $finding['severity'], array( 'high', 'critical' ), true ) ) {
				return true;
			}
		}

		return false;
	}
}
