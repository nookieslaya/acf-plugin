<?php
namespace AcfSchemaGuard\Cli;
use AcfSchemaGuard\Scanner\CodeUsageScannerService;
use AcfSchemaGuard\Scanner\ScannerConfiguration;
if ( ! defined( 'ABSPATH' ) ) { exit; }
final class ReportCommand {
	private $scanner;
	public function __construct( CodeUsageScannerService $scanner ) {
		$this->scanner = $scanner;
	}
	public function export( $args, $assoc_args ) {
		$path   = isset( $args[0] ) ? (string) $args[0] : '';
		$format = isset( $assoc_args['format'] ) ? strtolower( (string) $assoc_args['format'] ) : 'json';
		if ( '' === $path || ! in_array( $format, array( 'json', 'markdown' ), true ) ) { \WP_CLI::error( 'Provide an output path and --format=json or --format=markdown.' ); }
		if ( file_exists( $path ) && empty( $assoc_args['force'] ) ) { \WP_CLI::error( 'Report already exists. Use --force to replace it.' ); }
		$items = array();
		foreach ( $this->scanner->scan( ( new ScannerConfiguration() )->roots() ) as $reference ) {
			$items[] = $reference->to_array();
		}
		$content = 'json' === $format ? json_encode( $items, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) : $this->markdown( $items );
		if ( false === $content || false === file_put_contents( $path, $content ) ) { \WP_CLI::error( 'Could not write report.' ); }
		\WP_CLI::success( 'Code usage report exported: ' . $path );
	}
	private function markdown( array $items ) {
		$lines = array( '# ACF code usage report', '', '| Field | File | Line | Expression |', '| --- | --- | ---: | --- |' );
		foreach ( $items as $item ) {
			$lines[] = sprintf( '| `%s` | `%s` | %d | `%s` |', $item['field_name'], $item['path'], $item['line'], str_replace( '|', '\\|', $item['expression'] ) );
		}
		return implode( "\n", $lines ) . "\n";
	}
}
