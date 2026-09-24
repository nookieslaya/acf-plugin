<?php
/**
 * Protects access boundaries for the value-free migration-plan Admin actions.
 *
 * @package ACFSchemaGuard
 */

function asg_migration_admin_assert( $condition, $message ) {
	if ( ! $condition ) {
		throw new RuntimeException( $message );
	}
}

$source = file_get_contents( dirname( __DIR__ ) . '/includes/admin/class-admin-controller.php' );

asg_migration_admin_assert( false !== strpos( $source, 'admin_post_acf_schema_guard_prepare_migration_plan' ), 'The prepare action must be registered.' );
asg_migration_admin_assert( false !== strpos( $source, 'admin_post_acf_schema_guard_review_migration_plan' ), 'The review action must be registered.' );
asg_migration_admin_assert( false !== strpos( $source, 'assert_migration_plan_access' ), 'Migration actions must use a shared access guard.' );
asg_migration_admin_assert( false !== strpos( $source, 'SAFE_RENAME_MIGRATIONS' ), 'Migration actions must require the dedicated Pro capability.' );
asg_migration_admin_assert( false !== strpos( $source, "check_admin_referer( \$nonce_action )" ), 'Migration actions must verify a nonce.' );
asg_migration_admin_assert( false === strpos( $source, "name=\"execute_migration\"" ), 'The foundation must not render a migration execution control.' );
asg_migration_admin_assert( false === strpos( $source, 'update_post_meta(' ), 'The Admin controller must not write post meta.' );

echo "Migration plan Admin contract assertions passed.\n";
