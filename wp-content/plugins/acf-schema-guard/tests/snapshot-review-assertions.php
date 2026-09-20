<?php
/**
 * Lightweight contract checks for the local snapshot review workflow.
 */

define( 'ABSPATH', __DIR__ . '/' );

$acf_schema_guard_test_options = array();

function absint( $value ) { return abs( (int) $value ); }
function sanitize_text_field( $value ) { return trim( (string) $value ); }
function sanitize_textarea_field( $value ) { return trim( (string) $value ); }
function get_option( $key, $default = false ) {
	global $acf_schema_guard_test_options;
	return array_key_exists( $key, $acf_schema_guard_test_options ) ? $acf_schema_guard_test_options[ $key ] : $default;
}
function update_option( $key, $value ) {
	global $acf_schema_guard_test_options;
	$acf_schema_guard_test_options[ $key ] = $value;
	return true;
}

require_once dirname( __DIR__ ) . '/includes/review/class-snapshot-review.php';
require_once dirname( __DIR__ ) . '/includes/review/class-snapshot-review-service.php';

use AcfSchemaGuard\Review\SnapshotReview;
use AcfSchemaGuard\Review\SnapshotReviewService;

function asg_review_assert( $condition, $message ) {
	if ( ! $condition ) {
		fwrite( STDERR, "Assertion failed: {$message}\n" );
		exit( 1 );
	}
}

$service = new SnapshotReviewService();
$invalid = new SnapshotReview( array( 'id' => 'incomplete' ) );
asg_review_assert( ! $invalid->is_valid(), 'Incomplete review data is rejected.' );
$review  = $service->request( 'snapshot-one', 7, 'Ada Developer', 'Please review the field rename before release.' );

asg_review_assert( $review instanceof SnapshotReview, 'A valid request creates a pending review.' );
asg_review_assert( SnapshotReview::PENDING === $review->status(), 'New review is pending.' );
asg_review_assert( null === $service->request( 'snapshot-one', 8, 'Bob Reviewer', 'Duplicate request.' ), 'Only one pending request exists for a snapshot.' );
asg_review_assert( null === $service->approved_for( 'snapshot-one' ), 'Pending review cannot approve a baseline.' );

$decision = $service->decide( $review->id(), SnapshotReview::APPROVED, 8, 'Bob Reviewer', 'Checked the impact and code references.' );
asg_review_assert( $decision instanceof SnapshotReview, 'A pending request can be decided.' );
asg_review_assert( SnapshotReview::APPROVED === $decision->status(), 'Decision records approval.' );
asg_review_assert( $service->approved_for( 'snapshot-one' ) instanceof SnapshotReview, 'Approved review is available for baseline protection.' );
asg_review_assert( null === $service->decide( $review->id(), SnapshotReview::REJECTED, 9, 'Cara', 'Changing a completed review.' ), 'Completed review decisions are immutable.' );

$next = $service->request( 'snapshot-one', 7, 'Ada Developer', 'A revised schema needs a new review.' );
asg_review_assert( $next instanceof SnapshotReview, 'A completed request can be followed by a new review.' );
asg_review_assert( SnapshotReview::PENDING === $service->latest_for( 'snapshot-one' )->status(), 'Newest review controls the snapshot state.' );
asg_review_assert( null === $service->approved_for( 'snapshot-one' ), 'A new pending review supersedes prior approval for baseline protection.' );

echo "Snapshot review assertions passed.\n";
