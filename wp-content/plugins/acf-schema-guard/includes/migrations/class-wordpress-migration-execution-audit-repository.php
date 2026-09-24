<?php
namespace AcfSchemaGuard\Migrations;
if ( ! defined( 'ABSPATH' ) ) { exit; }
final class WordPressMigrationExecutionAuditRepository implements MigrationExecutionAuditRepository {
	private $wpdb;
	public function __construct( $wpdb ) { $this->wpdb = $wpdb; }
	public function start( MigrationExecution $execution ) {
		$data = $execution->to_array();
		return false !== $this->wpdb->insert( MigrationExecutionTable::executions_table( $this->wpdb ), $data );
	}
	public function complete( MigrationExecution $execution ) {
		$data = $execution->to_array();
		unset( $data['id'], $data['plan_id'], $data['finding_fingerprint'], $data['current_schema_hash'], $data['requested_by'], $data['requested_at'] );
		return false !== $this->wpdb->update( MigrationExecutionTable::executions_table( $this->wpdb ), $data, array( 'id' => $execution->to_array()['id'] ) );
	}
	public function record( MigrationBackupJournal $journal ) {
		return false !== $this->wpdb->insert( MigrationExecutionTable::journal_table( $this->wpdb ), $journal->to_array() );
	}
}
