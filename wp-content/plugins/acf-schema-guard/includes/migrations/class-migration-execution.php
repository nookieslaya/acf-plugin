<?php
/**
 * Immutable, value-free audit record for one bounded migration attempt.
 *
 * @package ACFSchemaGuard
 */

namespace AcfSchemaGuard\Migrations;

use InvalidArgumentException;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class MigrationExecution {
	const RUNNING   = 'running';
	const COMPLETED = 'completed';
	const FAILED    = 'failed';

	private $data;

	public function __construct( array $data ) {
		$required = array( 'id', 'plan_id', 'finding_fingerprint', 'current_schema_hash', 'status', 'requested_by', 'requested_at', 'selected_count', 'copied_count', 'skipped_count', 'conflict_count', 'failed_count' );
		foreach ( $required as $key ) {
			if ( ! array_key_exists( $key, $data ) ) {
				throw new InvalidArgumentException( 'Migration execution is missing ' . $key . '.' );
			}
		}
		if ( ! in_array( (string) $data['status'], array( self::RUNNING, self::COMPLETED, self::FAILED ), true ) || '' === (string) $data['id'] || '' === (string) $data['plan_id'] ) {
			throw new InvalidArgumentException( 'Migration execution is invalid.' );
		}
		$this->data = array(
			'id' => (string) $data['id'], 'plan_id' => (string) $data['plan_id'], 'finding_fingerprint' => (string) $data['finding_fingerprint'], 'current_schema_hash' => (string) $data['current_schema_hash'], 'status' => (string) $data['status'], 'requested_by' => max( 0, (int) $data['requested_by'] ), 'requested_at' => (string) $data['requested_at'], 'completed_at' => isset( $data['completed_at'] ) ? (string) $data['completed_at'] : '',
			'selected_count' => max( 0, (int) $data['selected_count'] ), 'copied_count' => max( 0, (int) $data['copied_count'] ), 'skipped_count' => max( 0, (int) $data['skipped_count'] ), 'conflict_count' => max( 0, (int) $data['conflict_count'] ), 'failed_count' => max( 0, (int) $data['failed_count'] ),
		);
	}

	public function to_array() { return $this->data; }

	public function with_results( $copied, $skipped, $conflicts, $failed, $completed_at ) {
		$data = $this->data;
		$data['status'] = 0 < (int) $failed ? self::FAILED : self::COMPLETED;
		$data['copied_count'] = max( 0, (int) $copied );
		$data['skipped_count'] = max( 0, (int) $skipped );
		$data['conflict_count'] = max( 0, (int) $conflicts );
		$data['failed_count'] = max( 0, (int) $failed );
		$data['completed_at'] = (string) $completed_at;
		return new self( $data );
	}
}
