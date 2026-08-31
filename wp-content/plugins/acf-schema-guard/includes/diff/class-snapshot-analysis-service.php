<?php
namespace AcfSchemaGuard\Diff;
use AcfSchemaGuard\Snapshots\SchemaSnapshot;
if ( ! defined( 'ABSPATH' ) ) { exit; }
final class SnapshotAnalysisService {
	private $differ;
	private $classifier;
	public function __construct( SchemaDiffer $differ, RiskClassifier $classifier ) { $this->differ = $differ; $this->classifier = $classifier; }
	public function analyze( SchemaSnapshot $before, SchemaSnapshot $after ) { $diff = $this->differ->compare( $before->schema(), $after->schema() ); return new SnapshotAnalysis( $diff, $this->classifier->classify( $diff ) ); }
}
