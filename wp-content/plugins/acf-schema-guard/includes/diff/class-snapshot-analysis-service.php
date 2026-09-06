<?php
namespace AcfSchemaGuard\Diff;
use AcfSchemaGuard\Snapshots\SchemaSnapshot;
if ( ! defined( 'ABSPATH' ) ) { exit; }
final class SnapshotAnalysisService {
	private $differ;
	private $classifier;
	private $explainer;
	public function __construct( SchemaDiffer $differ, RiskClassifier $classifier, $explainer = null ) { $this->differ = $differ; $this->classifier = $classifier; $this->explainer = null === $explainer ? new SchemaChangeExplainer() : $explainer; }
	public function analyze( SchemaSnapshot $before, SchemaSnapshot $after ) { return $this->analyze_schemas( $before->schema(), $after->schema() ); }
	public function analyze_schemas( array $before, array $after ) { $diff = $this->differ->compare( $before, $after ); return new SnapshotAnalysis( $diff, $this->classifier->classify( $diff ), $this->explainer ); }
}
