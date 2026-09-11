<?php
namespace AcfSchemaGuard\Cli;
use AcfSchemaGuard\Diff\SnapshotAnalysisService;
use AcfSchemaGuard\Snapshots\SnapshotRepository;
use AcfSchemaGuard\Licensing\RiskPolicyService;
if ( ! defined( 'ABSPATH' ) ) { exit; }
final class CheckCommand {
	private $snapshots;
	private $analysis_service;
	private $risk_policy_service;

	public function __construct( SnapshotRepository $snapshots, SnapshotAnalysisService $analysis_service, $risk_policy_service = null ) {
		$this->snapshots = $snapshots;
		$this->analysis_service = $analysis_service;
		$this->risk_policy_service = $risk_policy_service instanceof RiskPolicyService ? $risk_policy_service : null;
	}

	public function check( $args, $assoc_args ) {
		if ( 2 !== count( $args ) ) {
			\WP_CLI::error( 'Provide exactly two snapshot IDs: before-id and after-id.' );
		}

		$before = $this->snapshots->find( $args[0] );
		$after = $this->snapshots->find( $args[1] );
		if ( null === $before || null === $after ) {
			\WP_CLI::error( 'One or both snapshots could not be found.' );
		}

		$analysis = $this->analysis_service->analyze( $before, $after )->to_array();
		$format = isset( $assoc_args['format'] ) ? strtolower( (string) $assoc_args['format'] ) : 'table';
		if ( ! in_array( $format, array( 'table', 'json' ), true ) ) {
			\WP_CLI::error( sprintf( 'Unsupported format: %s. Use table or json.', $format ) );
		}

		if ( 'json' === $format ) {
			\WP_CLI::line( json_encode( $analysis, JSON_PRETTY_PRINT ) );
		} else {
			$this->table( $analysis['findings'] );
		}

		if ( isset( $assoc_args['fail-on-breaking'] ) && $this->has_failing( $analysis['findings'] ) ) {
			\WP_CLI::error( null === $this->risk_policy_service ? 'Breaking schema changes found.' : 'Schema changes exceed the active risk policy.' );
		}
	}

	private function table( $findings ) {
		if ( empty( $findings ) ) {
			\WP_CLI::success( 'No schema changes found.' );
			return;
		}

		$formatter = new FindingOutputFormatter();
		\WP_CLI\Utils\format_items( 'table', $formatter->table_items( $findings ), $formatter->table_fields() );
	}

	private function has_failing( $findings ) {
		$policy = null !== $this->risk_policy_service ? $this->risk_policy_service->policy() : null;
		$levels = array( 'safe' => 0, 'warning' => 1, 'high' => 2, 'critical' => 3 );
		foreach ( $findings as $finding ) {
			$severity = isset( $finding['severity'] ) ? $finding['severity'] : '';
			$fails = null !== $policy ? $policy->fails( $severity ) : isset( $levels[ $severity ] ) && $levels[ $severity ] >= $levels['high'];
			if ( $fails ) {
				return true;
			}
		}

		return false;
	}
}
