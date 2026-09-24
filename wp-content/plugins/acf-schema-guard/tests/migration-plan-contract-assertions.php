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

function asg_migration_plan_assert( $condition, $message ) {
	if ( ! $condition ) {
		throw new RuntimeException( $message );
	}
}

$capabilities = \AcfSchemaGuard\Licensing\ProCapabilities::free_validation();
asg_migration_plan_assert( ! in_array( \AcfSchemaGuard\Licensing\ProCapabilities::SAFE_RENAME_MIGRATIONS, $capabilities, true ), 'The migration capability must not be granted by Free validation state.' );
asg_migration_plan_assert( in_array( \AcfSchemaGuard\Licensing\ProCapabilities::SAFE_RENAME_MIGRATIONS, \AcfSchemaGuard\Licensing\ProCapabilities::all(), true ), 'The migration capability must be known to Pro entitlements.' );

$plan = new \AcfSchemaGuard\Migrations\MigrationPlan( array( 'id' => 'a9c8a1e1-0123-4123-8123-123456789abc', 'old_name' => 'hero_title', 'new_name' => 'hero_heading', 'finding_fingerprint' => str_repeat( 'a', 64 ), 'baseline_snapshot_id' => '', 'current_schema_hash' => str_repeat( 'b', 64 ), 'old_record_count' => 4, 'conflict_count' => 1, 'candidate_count' => 999, 'status' => \AcfSchemaGuard\Migrations\MigrationPlan::DRAFT, 'created_by' => 3, 'created_at' => '2026-09-22 12:00:00' ) );
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

echo "Migration plan contract assertions passed.\n";
