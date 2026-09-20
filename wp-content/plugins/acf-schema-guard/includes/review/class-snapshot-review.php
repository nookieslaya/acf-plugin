<?php
/**
 * Represents one auditable local decision about a stored schema snapshot.
 *
 * @package ACFSchemaGuard
 */

namespace AcfSchemaGuard\Review;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class SnapshotReview {
	const PENDING  = 'pending';
	const APPROVED = 'approved';
	const REJECTED = 'rejected';

	private $data;

	public function __construct( array $data ) {
		$this->data = array(
			'id'                    => isset( $data['id'] ) ? (string) $data['id'] : '',
			'snapshot_id'           => isset( $data['snapshot_id'] ) ? (string) $data['snapshot_id'] : '',
			'status'                => isset( $data['status'] ) ? (string) $data['status'] : self::PENDING,
			'requester_id'          => isset( $data['requester_id'] ) ? absint( $data['requester_id'] ) : 0,
			'requester_name'        => isset( $data['requester_name'] ) ? sanitize_text_field( $data['requester_name'] ) : '',
			'request_note'          => isset( $data['request_note'] ) ? sanitize_textarea_field( $data['request_note'] ) : '',
			'requested_at'          => isset( $data['requested_at'] ) ? (string) $data['requested_at'] : '',
			'reviewer_id'           => isset( $data['reviewer_id'] ) ? absint( $data['reviewer_id'] ) : 0,
			'reviewer_name'         => isset( $data['reviewer_name'] ) ? sanitize_text_field( $data['reviewer_name'] ) : '',
			'decision_note'         => isset( $data['decision_note'] ) ? sanitize_textarea_field( $data['decision_note'] ) : '',
			'decided_at'            => isset( $data['decided_at'] ) ? (string) $data['decided_at'] : '',
		);
	}

	public function is_valid() {
		return '' !== $this->data['id']
			&& '' !== $this->data['snapshot_id']
			&& in_array( $this->data['status'], array( self::PENDING, self::APPROVED, self::REJECTED ), true )
			&& 0 < $this->data['requester_id']
			&& '' !== $this->data['requester_name']
			&& '' !== $this->data['request_note']
			&& false !== strtotime( $this->data['requested_at'] )
			&& ( self::PENDING === $this->data['status'] || $this->has_decision() );
	}

	public function can_be_decided() {
		return $this->is_valid() && self::PENDING === $this->data['status'];
	}

	public function has_decision() {
		return 0 < $this->data['reviewer_id']
			&& '' !== $this->data['reviewer_name']
			&& '' !== $this->data['decision_note']
			&& false !== strtotime( $this->data['decided_at'] );
	}

	public function with_decision( $status, $reviewer_id, $reviewer_name, $decision_note, $decided_at ) {
		if ( ! $this->can_be_decided() || ! in_array( $status, array( self::APPROVED, self::REJECTED ), true ) ) {
			return null;
		}

		return new self(
			array_merge(
				$this->data,
				array(
					'status'        => $status,
					'reviewer_id'   => $reviewer_id,
					'reviewer_name' => $reviewer_name,
					'decision_note' => $decision_note,
					'decided_at'    => $decided_at,
				)
			)
		);
	}

	public function id() { return $this->data['id']; }
	public function snapshot_id() { return $this->data['snapshot_id']; }
	public function status() { return $this->data['status']; }
	public function requester_name() { return $this->data['requester_name']; }
	public function request_note() { return $this->data['request_note']; }
	public function requested_at() { return $this->data['requested_at']; }
	public function reviewer_name() { return $this->data['reviewer_name']; }
	public function decision_note() { return $this->data['decision_note']; }
	public function decided_at() { return $this->data['decided_at']; }
	public function to_array() { return $this->data; }
}
