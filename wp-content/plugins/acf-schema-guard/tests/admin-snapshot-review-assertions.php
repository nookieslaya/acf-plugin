<?php
/**
 * Source-level contracts for the Admin review workflow wiring.
 */

$source = file_get_contents( dirname( __DIR__ ) . '/includes/admin/class-admin-controller.php' );

function asg_admin_review_assert( $condition, $message ) {
	if ( ! $condition ) {
		fwrite( STDERR, "Assertion failed: {$message}\n" );
		exit( 1 );
	}
}

asg_admin_review_assert( false !== strpos( $source, 'admin_post_acf_schema_guard_request_snapshot_review' ), 'Admin registers review-request action.' );
asg_admin_review_assert( false !== strpos( $source, 'admin_post_acf_schema_guard_decide_snapshot_review' ), 'Admin registers review-decision action.' );
asg_admin_review_assert( false !== strpos( $source, '$this->review_service()->approved_for( $id )' ), 'Baseline action requires an approved review.' );
asg_admin_review_assert( false !== strpos( $source, 'Requires approved review' ), 'History explains why a baseline action is unavailable.' );
asg_admin_review_assert( false !== strpos( $source, 'Team review' ), 'History and Overview expose the team review workflow.' );

echo "Admin snapshot review assertions passed.\n";
