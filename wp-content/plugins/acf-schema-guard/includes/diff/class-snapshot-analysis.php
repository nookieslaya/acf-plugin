<?php
namespace AcfSchemaGuard\Diff;
if ( ! defined( 'ABSPATH' ) ) { exit; }
final class SnapshotAnalysis {
	private $diff;
	private $findings;
	private $explainer;
	public function __construct( SchemaDiff $diff, array $findings, SchemaChangeExplainer $explainer ) {
		$this->diff = $diff;
		$this->findings = $findings;
		$this->explainer = $explainer;
	}
	public function to_array() {
		$findings = array();
		foreach ( $this->findings as $finding ) {
			if ( $finding instanceof RiskFinding ) {
				$finding_data = $finding->to_array();
				$finding_data['explanation'] = $this->explainer->explain( $finding_data['change'] );
				$findings[] = $finding_data;
			}
		}
		return array( 'diff' => $this->diff->to_array(), 'findings' => $findings );
	}
}
