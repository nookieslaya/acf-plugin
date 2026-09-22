<?php
/**
 * Source-level safety contracts for Safe Rename Assistant wiring.
 */

$admin_source = file_get_contents( dirname( __DIR__ ) . '/includes/admin/class-admin-controller.php' );
$repository_source = file_get_contents( dirname( __DIR__ ) . '/includes/impact/class-wordpress-rename-dry-run-repository.php' );

function asg_safe_rename_contract_assert( $condition, $message ) {
	if ( ! $condition ) {
		fwrite( STDERR, "Assertion failed: {$message}\n" );
		exit( 1 );
	}
}

asg_safe_rename_contract_assert( false !== strpos( $admin_source, 'Safe Rename Assistant' ), 'Changes renders the Safe Rename Assistant.' );
asg_safe_rename_contract_assert( false !== strpos( $admin_source, 'This assistant never changes data.' ), 'Changes states the no-write guarantee.' );
asg_safe_rename_contract_assert( false !== strpos( $repository_source, 'new_meta.meta_key = %s' ), 'Dry-run checks same-record new-key conflicts.' );
asg_safe_rename_contract_assert( false === strpos( $repository_source, 'meta_value' ), 'Dry-run repository must not read meta values.' );
asg_safe_rename_contract_assert( false !== strpos( $repository_source, 'LIMIT %d' ), 'Dry-run sample query is bounded.' );

echo "Safe rename Admin contract assertions passed.\n";
