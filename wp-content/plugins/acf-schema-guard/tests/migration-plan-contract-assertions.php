<?php
/**
 * Protects the value-free Pro migration-plan foundation.
 *
 * @package ACFSchemaGuard
 */

define( 'ABSPATH', __DIR__ . '/' );

require_once dirname( __DIR__ ) . '/includes/licensing/class-pro-capabilities.php';
require_once dirname( __DIR__ ) . '/includes/licensing/class-license-state.php';
require_once dirname( __DIR__ ) . '/includes/migrations/class-migration-plan.php';
require_once dirname( __DIR__ ) . '/includes/migrations/class-migration-plan-table.php';
require_once dirname( __DIR__ ) . '/includes/migrations/class-migration-execution.php';
require_once dirname( __DIR__ ) . '/includes/migrations/class-migration-backup-journal.php';
require_once dirname( __DIR__ ) . '/includes/migrations/class-migration-execution-table.php';

function asg_migration_plan_assert( $condition, $message ) {
	if ( ! $condition ) {
		throw new RuntimeException( $message );
	}
}

$capabilities = \AcfSchemaGuard\Licensing\ProCapabilities::free_validation();
asg_migration_plan_assert( ! in_array( \AcfSchemaGuard\Licensing\ProCapabilities::SAFE_RENAME_MIGRATIONS, $capabilities, true ), 'The migration capability must not be granted by Free validation state.' );
asg_migration_plan_assert( in_array( \AcfSchemaGuard\Licensing\ProCapabilities::SAFE_RENAME_MIGRATIONS, \AcfSchemaGuard\Licensing\ProCapabilities::all(), true ), 'The migration capability must be known to Pro entitlements.' );

$plan = new \AcfSchemaGuard\Migrations\MigrationPlan( array( 'id' => 'a9c8a1e1-0123-4123-8123-123456789abc', 'old_name' => 'hero_title', 'new_name' => 'hero_heading', 'field_key' => 'field_hero_title', 'finding_fingerprint' => str_repeat( 'a', 64 ), 'baseline_snapshot_id' => '', 'current_schema_hash' => str_repeat( 'b', 64 ), 'old_record_count' => 4, 'conflict_count' => 1, 'candidate_count' => 999, 'status' => \AcfSchemaGuard\Migrations\MigrationPlan::DRAFT, 'created_by' => 3, 'created_at' => '2026-09-22 12:00:00' ) );
$data = $plan->to_array();
asg_migration_plan_assert( 3 === $data['candidate_count'], 'Candidate count must be derived from old records minus conflicts.' );
asg_migration_plan_assert( false === array_key_exists( 'meta_value', $data ), 'A migration plan must never contain a field value.' );

$invalid = false;
try {
	new \AcfSchemaGuard\Migrations\MigrationPlan( array_merge( $data, array( 'new_name' => '' ) ) );
} catch ( InvalidArgumentException $exception ) {
	$invalid = true;
}
asg_migration_plan_assert( $invalid, 'A migration plan must reject an empty new field name.' );

$table_source = file_get_contents( dirname( __DIR__ ) . '/includes/migrations/class-migration-plan-table.php' );
asg_migration_plan_assert( false === strpos( $table_source, 'meta_value' ), 'The migration plan table must not contain post-meta values.' );
asg_migration_plan_assert( false === strpos( $table_source, 'wp_postmeta' ), 'The migration plan table must not target WordPress content metadata.' );

$execution = new \AcfSchemaGuard\Migrations\MigrationExecution( array( 'id' => 'b9c8a1e1-0123-4123-8123-123456789abc', 'plan_id' => $data['id'], 'finding_fingerprint' => str_repeat( 'a', 64 ), 'current_schema_hash' => str_repeat( 'b', 64 ), 'status' => \AcfSchemaGuard\Migrations\MigrationExecution::COMPLETED, 'requested_by' => 3, 'requested_at' => '2026-09-24 12:00:00', 'selected_count' => 1, 'copied_count' => 1, 'skipped_count' => 0, 'conflict_count' => 0, 'failed_count' => 0 ) );
asg_migration_plan_assert( false === array_key_exists( 'meta_value', $execution->to_array() ), 'An execution record must never contain a field value.' );

$journal = new \AcfSchemaGuard\Migrations\MigrationBackupJournal( array( 'execution_id' => $execution->to_array()['id'], 'post_id' => 42, 'source_meta_id' => 12, 'source_key' => 'hero_title', 'target_meta_id' => 13, 'target_key' => 'hero_heading', 'source_reference_meta_id' => 14, 'source_reference_key' => '_hero_title', 'target_reference_meta_id' => 15, 'target_reference_key' => '_hero_heading', 'created_at' => '2026-09-24 12:00:00' ) );
asg_migration_plan_assert( false === array_key_exists( 'meta_value', $journal->to_array() ), 'A backup journal must never contain a field value.' );

$execution_table_source = file_get_contents( dirname( __DIR__ ) . '/includes/migrations/class-migration-execution-table.php' );
asg_migration_plan_assert( false === strpos( $execution_table_source, 'meta_value' ), 'Execution tables must not contain post-meta values.' );

echo "Migration plan contract assertions passed.\n";
