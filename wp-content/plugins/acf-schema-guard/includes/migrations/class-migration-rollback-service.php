<?php
/**
 * Removes only target meta rows recorded by a completed migration execution.
 *
 * @package ACFSchemaGuard
 */

namespace AcfSchemaGuard\Migrations;

if ( ! defined( 'ABSPATH' ) ) { exit; }

final class MigrationRollbackService {
	private $wpdb;

	public function __construct( $wpdb ) { $this->wpdb = $wpdb; }

	public function rollback( $execution_id ) {
		$executions = MigrationExecutionTable::executions_table( $this->wpdb );
		$status = $this->wpdb->get_var( $this->wpdb->prepare( "SELECT status FROM {$executions} WHERE id = %s", (string) $execution_id ) );
		if ( MigrationExecution::COMPLETED !== $status ) { return 0; }
		$journal = MigrationExecutionTable::journal_table( $this->wpdb );
		$rows = $this->wpdb->get_results( $this->wpdb->prepare( "SELECT target_meta_id, target_key, target_reference_meta_id, target_reference_key FROM {$journal} WHERE execution_id = %s", (string) $execution_id ), ARRAY_A );
		$removed = 0;
		foreach ( $rows as $row ) {
			$removed += (int) $this->wpdb->delete( $this->wpdb->postmeta, array( 'meta_id' => (int) $row['target_meta_id'], 'meta_key' => $row['target_key'] ), array( '%d', '%s' ) );
			$removed += (int) $this->wpdb->delete( $this->wpdb->postmeta, array( 'meta_id' => (int) $row['target_reference_meta_id'], 'meta_key' => $row['target_reference_key'] ), array( '%d', '%s' ) );
		}
		$this->wpdb->update( $executions, array( 'status' => 'rolled_back', 'completed_at' => gmdate( 'Y-m-d H:i:s' ) ), array( 'id' => (string) $execution_id ), array( '%s', '%s' ), array( '%s' ) );
		return $removed;
	}

	public function history( $limit = 20 ) {
		$table = MigrationExecutionTable::executions_table( $this->wpdb );
		return $this->wpdb->get_results( $this->wpdb->prepare( "SELECT id, plan_id, status, requested_at, completed_at, selected_count, copied_count, skipped_count, conflict_count, failed_count FROM {$table} ORDER BY requested_at DESC LIMIT %d", max( 1, min( 50, (int) $limit ) ) ), ARRAY_A );
	}

	public function journal( $execution_id ) {
		$table = MigrationExecutionTable::journal_table( $this->wpdb );
		return $this->wpdb->get_results( $this->wpdb->prepare( "SELECT post_id, source_meta_id, source_key, target_meta_id, target_key, source_reference_meta_id, target_reference_meta_id, created_at FROM {$table} WHERE execution_id = %s", (string) $execution_id ), ARRAY_A );
	}
}
