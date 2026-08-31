<?php
/**
 * Provides the read-only WP-CLI PHP ACF usage scan command.
 *
 * @package ACFSchemaGuard
 */

namespace AcfSchemaGuard\Cli;

use AcfSchemaGuard\Scanner\CodeUsageScannerService;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class ScanCommand {
	/**
	 * Scanner service composed from supported strategies.
	 *
	 * @var CodeUsageScannerService
	 */
	private $scanner;

	/**
	 * @param CodeUsageScannerService $scanner Scanner service.
	 */
	public function __construct( CodeUsageScannerService $scanner ) {
		$this->scanner = $scanner;
	}

	/**
	 * Scans explicit source roots for supported PHP ACF references.
	 *
	 * ## OPTIONS

	 * <source-root>...
	 * : Read-only source directories to scan.
	 *
	 * [--format=<format>]
	 * : Output format: table or json.
	 *
	 * @param string[]            $args Source root arguments.
	 * @param array<string,mixed> $assoc_args Named command arguments.
	 * @return void
	 */
	public function scan( $args, $assoc_args ) {
		$roots      = $this->source_roots( $args );
		$format     = $this->output_format( $assoc_args );
		$references = $this->scanner->scan( $roots );
		$items      = array();

		foreach ( $references as $reference ) {
			$items[] = $reference->to_array();
		}

		if ( 'json' === $format ) {
			$json = json_encode( $items, JSON_PRETTY_PRINT );

			if ( false === $json ) {
				\WP_CLI::error( 'Could not encode scan results as JSON.' );
			}

			\WP_CLI::line( $json );

			return;
		}

		if ( empty( $items ) ) {
			\WP_CLI::success( 'No supported ACF field references found.' );

			return;
		}

		\WP_CLI\Utils\format_items(
			'table',
			$items,
			array( 'field_name', 'strategy', 'path', 'line', 'expression' )
		);
	}

	/**
	 * Validates and resolves the supplied source directories.
	 *
	 * @param string[] $args Source root arguments.
	 * @return string[]
	 */
	private function source_roots( $args ) {
		if ( empty( $args ) ) {
			\WP_CLI::error( 'Provide at least one readable source-root directory.' );
		}

		$roots = array();

		foreach ( $args as $root ) {
			$path = realpath( (string) $root );

			if ( false === $path || ! is_dir( $path ) || ! is_readable( $path ) ) {
				\WP_CLI::error( sprintf( 'Source root is not a readable directory: %s', $root ) );
			}

			$roots[] = $path;
		}

		return $roots;
	}

	/**
	 * Validates the requested output format.
	 *
	 * @param array<string,mixed> $assoc_args Named command arguments.
	 * @return string
	 */
	private function output_format( $assoc_args ) {
		$format = isset( $assoc_args['format'] ) ? strtolower( (string) $assoc_args['format'] ) : 'table';

		if ( ! in_array( $format, array( 'table', 'json' ), true ) ) {
			\WP_CLI::error( sprintf( 'Unsupported format: %s. Use table or json.', $format ) );
		}

		return $format;
	}
}
