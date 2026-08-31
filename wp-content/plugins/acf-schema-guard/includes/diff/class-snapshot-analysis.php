<?php
namespace AcfSchemaGuard\Diff;
if ( ! defined( 'ABSPATH' ) ) { exit; }
final class SnapshotAnalysis {
	private $diff;
	private $findings;
	public function __construct( SchemaDiff $diff, array $findings ) { $this->diff = $diff; $this->findings = $findings; }
	public function to_array() { $findings = array(); foreach ( $this->findings as $finding ) { if ( $finding instanceof RiskFinding ) { $findings[] = $finding->to_array(); } } return array( 'diff' => $this->diff->to_array(), 'findings' => $findings ); }
}
