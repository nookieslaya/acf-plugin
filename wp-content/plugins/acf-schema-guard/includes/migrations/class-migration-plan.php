<?php
/**
 * Immutable, value-free record for a reviewed direct field-name migration.
 *
 * @package ACFSchemaGuard
 */

namespace AcfSchemaGuard\Migrations;

use InvalidArgumentException;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class MigrationPlan {
	const DRAFT    = 'draft';
	const REVIEWED = 'reviewed';
	const INVALID  = 'invalid';

	private $data;

	public function __construct( array $data ) {
		$this->data = $this->validated_data( $data );
	}

	public function id() {
		return $this->data['id'];
	}

	public function status() {
		return $this->data['status'];
	}

	public function fingerprint() {
		return $this->data['finding_fingerprint'];
	}

	public function schema_hash() {
		return $this->data['current_schema_hash'];
	}

	public function with_review( $reviewer_id, $review_note, $reviewed_at ) {
		if ( self::DRAFT !== $this->status() || '' === trim( (string) $review_note ) ) {
			return null;
		}

		return new self(
			array_merge(
				$this->data,
				array(
					'status'        => self::REVIEWED,
					'reviewed_by'   => max( 0, (int) $reviewer_id ),
					'reviewed_note' => trim( (string) $review_note ),
					'reviewed_at'   => (string) $reviewed_at,
				)
			)
		);
	}

	public function with_invalid() {
		if ( self::DRAFT !== $this->status() ) {
			return $this;
		}

		return new self( array_merge( $this->data, array( 'status' => self::INVALID ) ) );
	}

	public function to_array() {
		return $this->data;
	}

	private function validated_data( array $data ) {
		$required = array( 'id', 'old_name', 'new_name', 'field_key', 'finding_fingerprint', 'current_schema_hash', 'old_record_count', 'conflict_count', 'candidate_count', 'status', 'created_by', 'created_at' );
		foreach ( $required as $key ) {
			if ( ! array_key_exists( $key, $data ) ) {
				throw new InvalidArgumentException( 'Migration plan is missing ' . $key . '.' );
			}
		}

		$old_name = (string) $data['old_name'];
		$new_name = (string) $data['new_name'];
		$field_key = (string) $data['field_key'];
		if ( '' === $old_name || '' === $new_name || $old_name === $new_name || '' === $field_key ) {
			throw new InvalidArgumentException( 'Migration plan requires two different field names.' );
		}

		$status = (string) $data['status'];
		if ( ! in_array( $status, array( self::DRAFT, self::REVIEWED, self::INVALID ), true ) ) {
			throw new InvalidArgumentException( 'Migration plan status is invalid.' );
		}

		$old_count   = max( 0, (int) $data['old_record_count'] );
		$conflicts   = min( $old_count, max( 0, (int) $data['conflict_count'] ) );
		$candidates  = max( 0, $old_count - $conflicts );
		$baseline_id = isset( $data['baseline_snapshot_id'] ) ? (string) $data['baseline_snapshot_id'] : '';

		return array(
			'id'                  => (string) $data['id'],
			'old_name'            => $old_name,
			'new_name'            => $new_name,
			'field_key'           => $field_key,
			'finding_fingerprint' => (string) $data['finding_fingerprint'],
			'baseline_snapshot_id' => $baseline_id,
			'current_schema_hash' => (string) $data['current_schema_hash'],
			'old_record_count'    => $old_count,
			'conflict_count'      => $conflicts,
			'candidate_count'     => $candidates,
			'status'              => $status,
			'created_by'          => max( 0, (int) $data['created_by'] ),
			'created_at'          => (string) $data['created_at'],
			'reviewed_by'         => isset( $data['reviewed_by'] ) ? max( 0, (int) $data['reviewed_by'] ) : 0,
			'reviewed_note'       => isset( $data['reviewed_note'] ) ? (string) $data['reviewed_note'] : '',
			'reviewed_at'         => isset( $data['reviewed_at'] ) ? (string) $data['reviewed_at'] : '',
		);
	}
}
