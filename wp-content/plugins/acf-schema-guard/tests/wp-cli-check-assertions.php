<?php
require_once __DIR__ . '/wp-cli-diff-assertions.php';

require_once dirname( __DIR__ ) . '/includes/cli/class-check-command.php';

$check = new \AcfSchemaGuard\Cli\CheckCommand(
	new AcfSchemaGuardDiffRepository( array( $before->id() => $before, $after->id() => $after ) ),
	new \AcfSchemaGuard\Diff\SnapshotAnalysisService(
		new \AcfSchemaGuard\Diff\SchemaDiffer(),
		new \AcfSchemaGuard\Diff\RiskClassifier()
	)
);
$check->check( array( $before->id(), $after->id() ), array( 'fail-on-breaking' => true ) );
acf_schema_guard_diff_assert( ! empty( WP_CLI::$successes ), 'Safe check did not succeed.' );

$high_before = new \AcfSchemaGuard\Snapshots\SchemaSnapshot(
	'44444444-4444-4444-4444-444444444444',
	'test',
	array( 'schema_version' => 1, 'field_groups' => array( array( 'key' => 'group', 'fields' => array( array( 'key' => 'field', 'type' => 'text' ) ) ) ) ),
	'2026-01-03 00:00:00'
);
$high_after = new \AcfSchemaGuard\Snapshots\SchemaSnapshot(
	'55555555-5555-5555-5555-555555555555',
	'test',
	array( 'schema_version' => 1, 'field_groups' => array( array( 'key' => 'group', 'fields' => array( array( 'key' => 'field', 'type' => 'number' ) ) ) ) ),
	'2026-01-04 00:00:00'
);
$breaking_check = new \AcfSchemaGuard\Cli\CheckCommand(
	new AcfSchemaGuardDiffRepository( array( $high_before->id() => $high_before, $high_after->id() => $high_after ) ),
	new \AcfSchemaGuard\Diff\SnapshotAnalysisService( new \AcfSchemaGuard\Diff\SchemaDiffer(), new \AcfSchemaGuard\Diff\RiskClassifier() )
);
try {
	$breaking_check->check( array( $high_before->id(), $high_after->id() ), array( 'fail-on-breaking' => true ) );
	throw new \RuntimeException( 'Breaking change did not fail.' );
} catch ( \RuntimeException $exception ) {
	acf_schema_guard_diff_assert( false !== strpos( $exception->getMessage(), 'Breaking schema changes found.' ), 'Breaking error is wrong.' );
}
echo "WP-CLI check assertions passed.\n";
