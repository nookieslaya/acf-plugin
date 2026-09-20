<?php
/**
 * Stores bounded, local review records without altering schema snapshots.
 *
 * @package ACFSchemaGuard
 */

namespace AcfSchemaGuard\Review;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class SnapshotReviewService {
	const OPTION_NAME = 'acf_schema_guard_snapshot_reviews';
	const MAX_RECORDS = 100;

	public function all() {
		$reviews = array();
		foreach ( (array) get_option( self::OPTION_NAME, array() ) as $record ) {
			$review = new SnapshotReview( is_array( $record ) ? $record : array() );
			if ( $review->is_valid() ) {
				$reviews[] = $review;
			}
		}

		usort( $reviews, array( $this, 'newest_first' ) );
		return $reviews;
	}

	public function latest_for( $snapshot_id ) {
		foreach ( $this->all() as $review ) {
			if ( $review->snapshot_id() === (string) $snapshot_id ) {
				return $review;
			}
		}

		return null;
	}

	public function approved_for( $snapshot_id ) {
		$review = $this->latest_for( $snapshot_id );
		return $review && SnapshotReview::APPROVED === $review->status() ? $review : null;
	}

	public function pending() {
		return array_values( array_filter( $this->all(), static function( $review ) { return SnapshotReview::PENDING === $review->status(); } ) );
	}

	public function request( $snapshot_id, $requester_id, $requester_name, $request_note ) {
		$existing = $this->latest_for( $snapshot_id );
		if ( $existing && SnapshotReview::PENDING === $existing->status() ) {
			return null;
		}

		$review = new SnapshotReview(
			array(
				'id'             => $this->new_id( $snapshot_id ),
				'snapshot_id'    => $snapshot_id,
				'status'         => SnapshotReview::PENDING,
				'requester_id'   => $requester_id,
				'requester_name' => $requester_name,
				'request_note'   => $request_note,
				'requested_at'   => $this->timestamp(),
			)
		);

		return $review->is_valid() && $this->save( $review ) ? $review : null;
	}

	public function decide( $review_id, $status, $reviewer_id, $reviewer_name, $decision_note ) {
		$records = $this->all();
		foreach ( $records as $index => $review ) {
			if ( $review->id() !== (string) $review_id ) {
				continue;
			}

			$decision = $review->with_decision( $status, $reviewer_id, $reviewer_name, $decision_note, $this->timestamp() );
			if ( ! $decision || ! $decision->is_valid() ) {
				return null;
			}

			$records[ $index ] = $decision;
			return $this->replace( $records ) ? $decision : null;
		}

		return null;
	}

	private function save( SnapshotReview $review ) {
		$records   = $this->all();
		$records[] = $review;
		return $this->replace( $records );
	}

	private function replace( array $reviews ) {
		usort( $reviews, array( $this, 'newest_first' ) );
		$records = array_map( static function( $review ) { return $review->to_array(); }, array_slice( $reviews, 0, self::MAX_RECORDS ) );
		return update_option( self::OPTION_NAME, $records, false );
	}

	private function newest_first( SnapshotReview $left, SnapshotReview $right ) {
		return strcmp( $right->requested_at(), $left->requested_at() );
	}

	private function new_id( $snapshot_id ) {
		$seed = function_exists( 'wp_generate_uuid4' ) ? wp_generate_uuid4() : uniqid( '', true );
		return hash( 'sha256', (string) $snapshot_id . '|' . $seed );
	}

	/**
	 * Returns sortable UTC time including microseconds for same-second reviews.
	 *
	 * @return string
	 */
	private function timestamp() {
		$time = microtime( true );
		return gmdate( 'Y-m-d\\TH:i:s', (int) $time ) . sprintf( '.%06dZ', (int) ( ( $time - floor( $time ) ) * 1000000 ) );
	}
}
