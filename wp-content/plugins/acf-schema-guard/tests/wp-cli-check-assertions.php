<?php
require_once __DIR__ . '/wp-cli-diff-assertions.php';

require_once dirname( __DIR__ ) . '/includes/cli/class-check-command.php';

$check = new \AcfSchemaGuard\Cli\CheckCommand(
	new AcfSchemaGuardDiffRepository( array( $before->id() => $before, $after->id() => $after ) ),
	new \AcfSchemaGuard\Diff\SnapshotAnalysisService(
		new \AcfSchemaGuard\Diff\SchemaDiffer(),
		new \AcfSchemaGuard\Diff\RiskClassifier(),
		new \AcfSchemaGuard\Diff\SchemaChangeExplainer()
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
	new \AcfSchemaGuard\Diff\SnapshotAnalysisService( new \AcfSchemaGuard\Diff\SchemaDiffer(), new \AcfSchemaGuard\Diff\RiskClassifier(), new \AcfSchemaGuard\Diff\SchemaChangeExplainer() )
);
try {
	$breaking_check->check( array( $high_before->id(), $high_after->id() ), array( 'fail-on-breaking' => true ) );
	throw new \RuntimeException( 'Breaking change did not fail.' );
} catch ( \RuntimeException $exception ) {
	acf_schema_guard_diff_assert( false !== strpos( $exception->getMessage(), 'Breaking schema changes found.' ), 'Breaking error is wrong.' );
}
$field_item = null;
foreach ( WP_CLI::$items as $item ) {
	if ( 'field' === $item['node_type'] ) {
		$field_item = $item;
		break;
	}
}
acf_schema_guard_diff_assert( null !== $field_item && 'Field modified.' === $field_item['summary'], 'Check table summary is missing.' );
acf_schema_guard_diff_assert( 'Field type: "text" -> "number"' === $field_item['details'], 'Check table details are missing.' );

$rename_before = new \AcfSchemaGuard\Snapshots\SchemaSnapshot(
	'66666666-6666-4666-8666-666666666666',
	'test',
	array( 'schema_version' => 1, 'field_groups' => array( array( 'key' => 'group', 'fields' => array( array( 'key' => 'field', 'name' => 'old_name', 'type' => 'text' ) ) ) ) ),
	'2026-01-05 00:00:00'
);
$rename_after = new \AcfSchemaGuard\Snapshots\SchemaSnapshot(
	'77777777-7777-4777-8777-777777777777',
	'test',
	array( 'schema_version' => 1, 'field_groups' => array( array( 'key' => 'group', 'fields' => array( array( 'key' => 'field', 'name' => 'new_name', 'type' => 'text' ) ) ) ) ),
	'2026-01-06 00:00:00'
);
$rename_check = new \AcfSchemaGuard\Cli\CheckCommand(
	new AcfSchemaGuardDiffRepository( array( $rename_before->id() => $rename_before, $rename_after->id() => $rename_after ) ),
	new \AcfSchemaGuard\Diff\SnapshotAnalysisService( new \AcfSchemaGuard\Diff\SchemaDiffer(), new \AcfSchemaGuard\Diff\RiskClassifier(), new \AcfSchemaGuard\Diff\SchemaChangeExplainer() )
);

try {
	$rename_check->check( array( $rename_before->id(), $rename_after->id() ), array( 'fail-on-breaking' => true ) );
	throw new \RuntimeException( 'Field rename did not fail.' );
} catch ( \RuntimeException $exception ) {
	acf_schema_guard_diff_assert( false !== strpos( $exception->getMessage(), 'Breaking schema changes found.' ), 'Field rename check error is wrong.' );
}

$rename_item = null;
foreach ( WP_CLI::$items as $item ) {
	if ( 'field' === $item['node_type'] ) {
		$rename_item = $item;
		break;
	}
}
acf_schema_guard_diff_assert( null !== $rename_item && 'high' === $rename_item['severity'], 'Field rename severity is wrong.' );
acf_schema_guard_diff_assert( 'Field name changed.' === $rename_item['rationale'], 'Field rename rationale is wrong.' );
echo "WP-CLI check assertions passed.\n";
