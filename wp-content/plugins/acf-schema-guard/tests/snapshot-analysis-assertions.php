<?php
define( 'ABSPATH', __DIR__ . '/' );
foreach ( array( 'class-schema-change.php', 'class-schema-diff.php', 'class-schema-differ.php', 'class-risk-finding.php', 'class-risk-classifier.php', 'class-snapshot-analysis.php', 'class-snapshot-analysis-service.php' ) as $file ) { require_once dirname( __DIR__ ) . '/includes/diff/' . $file; }
require_once dirname( __DIR__ ) . '/includes/snapshots/class-schema-snapshot.php';
$schema = array( 'schema_version' => 1, 'field_groups' => array( array( 'key' => 'g', 'fields' => array( array( 'key' => 'f', 'type' => 'text' ) ) ) ) );
$changed = array( 'schema_version' => 1, 'field_groups' => array( array( 'key' => 'g', 'fields' => array( array( 'key' => 'f', 'type' => 'number' ) ) ) ) );
$a = new \AcfSchemaGuard\Snapshots\SchemaSnapshot( '123e4567-e89b-12d3-a456-426614174000', 'a', $schema, '2026-01-01 00:00:00' );
$b = new \AcfSchemaGuard\Snapshots\SchemaSnapshot( '123e4567-e89b-12d3-a456-426614174001', 'a', $changed, '2026-01-01 00:00:01' );
$service = new \AcfSchemaGuard\Diff\SnapshotAnalysisService( new \AcfSchemaGuard\Diff\SchemaDiffer(), new \AcfSchemaGuard\Diff\RiskClassifier() );
if ( 0 !== count( $service->analyze( $a, $a )->to_array()['findings'] ) || 0 === count( $service->analyze( $a, $b )->to_array()['findings'] ) ) { exit( 1 ); }
echo "Snapshot analysis assertions passed.\n";
