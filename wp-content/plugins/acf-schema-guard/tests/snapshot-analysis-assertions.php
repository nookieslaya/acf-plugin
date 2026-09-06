<?php
define( 'ABSPATH', __DIR__ . '/' );
foreach ( array( 'class-schema-change.php', 'class-schema-diff.php', 'class-schema-differ.php', 'class-risk-finding.php', 'class-risk-classifier.php', 'class-schema-change-explainer.php', 'class-snapshot-analysis.php', 'class-snapshot-analysis-service.php' ) as $file ) { require_once dirname( __DIR__ ) . '/includes/diff/' . $file; }
require_once dirname( __DIR__ ) . '/includes/snapshots/class-schema-snapshot.php';
$schema = array( 'schema_version' => 1, 'field_groups' => array( array( 'key' => 'g', 'fields' => array( array( 'key' => 'f', 'type' => 'text' ) ) ) ) );
$changed = array( 'schema_version' => 1, 'field_groups' => array( array( 'key' => 'g', 'fields' => array( array( 'key' => 'f', 'type' => 'number' ) ) ) ) );
$a = new \AcfSchemaGuard\Snapshots\SchemaSnapshot( '123e4567-e89b-12d3-a456-426614174000', 'a', $schema, '2026-01-01 00:00:00' );
$b = new \AcfSchemaGuard\Snapshots\SchemaSnapshot( '123e4567-e89b-12d3-a456-426614174001', 'a', $changed, '2026-01-01 00:00:01' );
$service = new \AcfSchemaGuard\Diff\SnapshotAnalysisService( new \AcfSchemaGuard\Diff\SchemaDiffer(), new \AcfSchemaGuard\Diff\RiskClassifier(), new \AcfSchemaGuard\Diff\SchemaChangeExplainer() );
$unchanged_analysis = $service->analyze( $a, $a )->to_array();
$changed_analysis   = $service->analyze( $a, $b )->to_array();
$field_finding      = null;
foreach ( $changed_analysis['findings'] as $finding ) {
	if ( 'field' === $finding['change']['node_type'] ) {
		$field_finding = $finding;
		break;
	}
}
if ( array() !== $unchanged_analysis['findings'] || null === $field_finding ) { exit( 1 ); }
if ( 'Field modified.' !== $field_finding['explanation']['summary'] || array( 'Field type: "text" -> "number"' ) !== $field_finding['explanation']['details'] ) { exit( 1 ); }
if ( ! isset( $field_finding['change'], $field_finding['severity'], $field_finding['rationale'] ) ) { exit( 1 ); }
$compatible_service = new \AcfSchemaGuard\Diff\SnapshotAnalysisService( new \AcfSchemaGuard\Diff\SchemaDiffer(), new \AcfSchemaGuard\Diff\RiskClassifier() );
if ( ! isset( $compatible_service->analyze( $a, $b )->to_array()['findings'][0]['explanation'] ) ) { exit( 1 ); }
echo "Snapshot analysis assertions passed.\n";
