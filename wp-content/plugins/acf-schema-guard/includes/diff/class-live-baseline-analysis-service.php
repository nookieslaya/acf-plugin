<?php
/**
 * Compares an approved snapshot with an in-memory current schema.
 *
 * @package ACFSchemaGuard
 */

namespace AcfSchemaGuard\Diff;

use AcfSchemaGuard\Snapshots\SchemaSnapshot;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class LiveBaselineAnalysisService {
	/** @var SnapshotAnalysisService */
	private $analysis_service;

	/** @param SnapshotAnalysisService $analysis_service Schema comparison service. */
	public function __construct( SnapshotAnalysisService $analysis_service ) {
		$this->analysis_service = $analysis_service;
	}

	/**
	 * @param SchemaSnapshot $baseline Approved stored baseline.
	 * @param array          $current_schema Fresh normalized schema.
	 * @return LiveBaselineAnalysis
	 */
	public function analyze( SchemaSnapshot $baseline, array $current_schema ) {
		return new LiveBaselineAnalysis(
			$baseline,
			$this->analysis_service->analyze_schemas( $baseline->schema(), $current_schema )
		);
	}

	/**
	 * @param string $message Unavailable-state explanation.
	 * @return LiveBaselineAnalysis
	 */
	public function unavailable( $message ) {
		return new LiveBaselineAnalysis( null, null, $message );
	}
}
