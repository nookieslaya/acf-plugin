<?php
namespace AcfSchemaGuard\Cli;
use AcfSchemaGuard\Licensing\CapabilityService;
use AcfSchemaGuard\Licensing\ProCapabilities;
use AcfSchemaGuard\Licensing\ReviewReportTemplate;
if ( ! defined( 'ABSPATH' ) ) { exit; }
final class ReleaseReportCommand {
	private $analysis;
	private $capabilities;
	private $policy;
	private $exceptions;
	public function __construct( $analysis, CapabilityService $capabilities, $policy = null, $exceptions = null ) { $this->analysis = $analysis; $this->capabilities = $capabilities; $this->policy = $policy; $this->exceptions = $exceptions; }
	public function export( $args, $assoc_args ) {
		$this->assert_pro_access(); $path = $this->path( $args ); $format = $this->format( $assoc_args ); $this->assert_writable( $path, $assoc_args ); $content = 'json' === $format ? wp_json_encode( $this->report(), JSON_PRETTY_PRINT ) : $this->markdown( $this->report(), $assoc_args ); if ( false === file_put_contents( $path, $content ) ) { \WP_CLI::error( 'Could not write release report.' ); } \WP_CLI::success( 'Release report exported: ' . $path );
	}
	private function assert_pro_access() { if ( ! $this->capabilities->can( ProCapabilities::REVIEW_READY_REPORTS )->is_allowed() ) { \WP_CLI::error( 'Review-ready reports are available in Pro. Existing code-usage export remains available in Free.' ); } }
	private function path( array $args ) { $path = isset( $args[0] ) ? (string) $args[0] : ''; if ( '' === $path ) { \WP_CLI::error( 'Provide an output path and --format=markdown or --format=json.' ); } return $path; }
	private function format( array $args ) { $format = isset( $args['format'] ) ? (string) $args['format'] : 'markdown'; if ( ! in_array( $format, array( 'markdown', 'json' ), true ) ) { \WP_CLI::error( 'Provide an output path and --format=markdown or --format=json.' ); } return $format; }
	private function assert_writable( $path, array $args ) { if ( file_exists( $path ) && empty( $args['force'] ) ) { \WP_CLI::error( 'Report already exists. Use --force to replace it.' ); } }
	private function report() { $live = call_user_func( $this->analysis ); if ( ! $live->is_available() ) { \WP_CLI::error( $live->message() ); } $data = $live->analysis()->to_array(); foreach ( $data['findings'] as &$finding ) { $exception = $this->exceptions ? $this->exceptions->active_for( $finding ) : null; $finding['approved_exception'] = $exception ? $exception->to_array() : null; } unset( $finding ); return array( 'schema_version' => 1, 'policy' => $this->policy ? $this->policy->policy()->fail_on() : 'high', 'findings' => $data['findings'] ); }
	private function markdown( array $report, array $assoc_args ) { $findings = $report['findings']; $summary = '# ACF Schema Guard release review\n\nPolicy: `' . $report['policy'] . '`. ' . count( $findings ) . " findings require review.\n"; $rows = array( '## Findings', '', '| Severity | Path | Exception | Rationale |', '| --- | --- | --- | --- |' ); foreach ( $findings as $finding ) { $change = $finding['change']; $exception = empty( $finding['approved_exception'] ) ? 'None' : 'Approved'; $rows[] = sprintf( '| %s | `%s` | %s | %s |', $finding['severity'], implode( '.', $change['path'] ), $exception, str_replace( '|', '\\|', $finding['rationale'] ) ); } $fallback = $summary . "\n" . implode( "\n", $rows ) . "\n"; return isset( $assoc_args['template'] ) ? ( new ReviewReportTemplate() )->render( $assoc_args['template'], $summary, implode( "\n", $rows ), $fallback ) : $fallback; }
}
