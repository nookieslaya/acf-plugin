<?php
/**
 * Focused assertions for read-only live baseline analysis.
 *
 * Run with: php tests/live-baseline-analysis-assertions.php
 */

define( 'ABSPATH', __DIR__ . '/' );

foreach ( array( 'class-schema-change.php', 'class-schema-diff.php', 'class-schema-differ.php', 'class-risk-finding.php', 'class-risk-classifier.php', 'class-schema-change-explainer.php', 'class-snapshot-analysis.php', 'class-snapshot-analysis-service.php', 'class-live-baseline-analysis.php', 'class-live-baseline-analysis-service.php' ) as $file ) {
	require_once dirname( __DIR__ ) . '/includes/diff/' . $file;
}

require_once dirname( __DIR__ ) . '/includes/snapshots/class-schema-snapshot.php';

function acf_schema_guard_live_baseline_assert( $condition, $message ) {
	if ( ! $condition ) {
		throw new RuntimeException( $message );
	}
}

$baseline_schema = array(
	'schema_version' => 1,
	'field_groups'   => array(
		array(
			'key'    => 'group_example',
			'fields' => array( array( 'key' => 'field_example', 'type' => 'text' ) ),
		),
	),
);
$changed_schema  = array(
	'schema_version' => 1,
	'field_groups'   => array(
		array(
			'key'    => 'group_example',
			'fields' => array( array( 'key' => 'field_example', 'type' => 'textarea' ) ),
		),
	),
);
$baseline = new \AcfSchemaGuard\Snapshots\SchemaSnapshot(
	'123e4567-e89b-12d3-a456-426614174000',
	'manual',
	$baseline_schema,
	'2026-09-09 12:00:00'
);
$service = new \AcfSchemaGuard\Diff\LiveBaselineAnalysisService(
	new \AcfSchemaGuard\Diff\SnapshotAnalysisService(
		new \AcfSchemaGuard\Diff\SchemaDiffer(),
		new \AcfSchemaGuard\Diff\RiskClassifier(),
		new \AcfSchemaGuard\Diff\SchemaChangeExplainer()
	)
);

$unchanged = $service->analyze( $baseline, $baseline_schema );
acf_schema_guard_live_baseline_assert( $unchanged->is_available(), 'Matching live schema should be available.' );
acf_schema_guard_live_baseline_assert( array() === $unchanged->analysis()->to_array()['findings'], 'Matching live schema should have no findings.' );

$changed = $service->analyze( $baseline, $changed_schema )->analysis()->to_array();
acf_schema_guard_live_baseline_assert( 'high' === $changed['findings'][1]['severity'], 'Live type change should retain high severity.' );
acf_schema_guard_live_baseline_assert( 'Field type changed.' === $changed['findings'][1]['rationale'], 'Live type change should retain its rationale.' );

$unavailable = $service->unavailable( 'ACF is unavailable.' );
acf_schema_guard_live_baseline_assert( ! $unavailable->is_available(), 'Unavailable live schema should be controlled.' );
acf_schema_guard_live_baseline_assert( 'ACF is unavailable.' === $unavailable->message(), 'Unavailable live schema should retain its message.' );

echo "Live baseline analysis assertions passed.\n";
