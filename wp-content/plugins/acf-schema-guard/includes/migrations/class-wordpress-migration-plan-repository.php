<?php
/**
 * Persists only plugin-owned migration-plan metadata.
 *
 * @package ACFSchemaGuard
 */

namespace AcfSchemaGuard\Migrations;

use RuntimeException;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class WordPressMigrationPlanRepository implements MigrationPlanRepository {
	private $wpdb;
	private $table_name;

	public function __construct( $wpdb ) {
		$this->wpdb       = $wpdb;
		$this->table_name = MigrationPlanTable::table_name( $wpdb );
	}

	public function insert( MigrationPlan $plan ) {
		if ( false === $this->wpdb->insert( $this->table_name, $this->database_data( $plan ) ) ) {
			throw new RuntimeException( 'Migration plan insert failed: ' . $this->wpdb->last_error );
		}

		return $plan;
	}

	public function find( $id ) {
		$row = $this->wpdb->get_row( $this->wpdb->prepare( "SELECT * FROM {$this->table_name} WHERE id = %s", (string) $id ), ARRAY_A );
		return is_array( $row ) ? new MigrationPlan( $row ) : null;
	}

	public function latest_for_fingerprint( $fingerprint ) {
		$row = $this->wpdb->get_row( $this->wpdb->prepare( "SELECT * FROM {$this->table_name} WHERE finding_fingerprint = %s ORDER BY created_at DESC LIMIT 1", (string) $fingerprint ), ARRAY_A );
		return is_array( $row ) ? new MigrationPlan( $row ) : null;
	}

	public function save( MigrationPlan $plan ) {
		if ( false === $this->wpdb->update( $this->table_name, $this->database_data( $plan ), array( 'id' => $plan->id() ) ) ) {
			throw new RuntimeException( 'Migration plan update failed: ' . $this->wpdb->last_error );
		}

		return $plan;
	}

	private function database_data( MigrationPlan $plan ) {
		$data = $plan->to_array();
		return array(
			'id'                  => $data['id'],
			'old_name'            => $data['old_name'],
			'new_name'            => $data['new_name'],
			'finding_fingerprint' => $data['finding_fingerprint'],
			'baseline_snapshot_id' => $data['baseline_snapshot_id'],
			'current_schema_hash' => $data['current_schema_hash'],
			'old_record_count'    => $data['old_record_count'],
			'conflict_count'      => $data['conflict_count'],
			'candidate_count'     => $data['candidate_count'],
			'status'              => $data['status'],
			'created_by'          => $data['created_by'],
			'created_at'          => $data['created_at'],
			'reviewed_by'         => $data['reviewed_by'],
			'reviewed_note'       => $data['reviewed_note'],
			'reviewed_at'         => '' === $data['reviewed_at'] ? null : $data['reviewed_at'],
		);
	}
}
