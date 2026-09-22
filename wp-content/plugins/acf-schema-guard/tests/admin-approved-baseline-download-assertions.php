<?php
/**
 * Source-level contracts for the approved baseline download workflow.
 */

$source = file_get_contents( dirname( __DIR__ ) . '/includes/admin/class-admin-controller.php' );

function asg_admin_baseline_download_assert( $condition, $message ) {
	if ( ! $condition ) {
		fwrite( STDERR, "Assertion failed: {$message}\n" );
		exit( 1 );
	}
}

asg_admin_baseline_download_assert( false !== strpos( $source, 'admin_post_acf_schema_guard_download_approved_baseline' ), 'Admin registers approved-baseline download action.' );
asg_admin_baseline_download_assert( false !== strpos( $source, "check_admin_referer( 'acf_schema_guard_download_approved_baseline' )" ), 'Approved-baseline download requires its nonce.' );
asg_admin_baseline_download_assert( false !== strpos( $source, '$this->baseline->snapshot()' ), 'Approved-baseline download reads the active baseline snapshot.' );
asg_admin_baseline_download_assert( false !== strpos( $source, '( new SchemaBaselineFile() )->encode( $baseline->schema() )' ), 'Approved-baseline download reuses the portable baseline format.' );
asg_admin_baseline_download_assert( false !== strpos( $source, 'filename="acf-schema-baseline.json"' ), 'Approved-baseline download uses the fixed Git baseline filename.' );
asg_admin_baseline_download_assert( false !== strpos( $source, 'WordPress does not write repository files or run Git commands.' ), 'History explains the safe Git handoff.' );

echo "Admin approved baseline download assertions passed.\n";
