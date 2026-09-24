<?php
/**
 * Prepares and reviews safe, value-free direct rename migration plans.
 *
 * @package ACFSchemaGuard
 */

namespace AcfSchemaGuard\Migrations;

use AcfSchemaGuard\Licensing\CapabilityService;
use AcfSchemaGuard\Licensing\ProCapabilities;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class MigrationPlanService {
	private $repository;
	private $capabilities;
	private $fingerprint_callback;
	private $id_callback;
	private $time_callback;

	public function __construct( MigrationPlanRepository $repository, CapabilityService $capabilities, $fingerprint_callback, $id_callback = null, $time_callback = null ) {
		$this->repository           = $repository;
		$this->capabilities         = $capabilities;
		$this->fingerprint_callback = $fingerprint_callback;
		$this->id_callback          = is_callable( $id_callback ) ? $id_callback : array( $this, 'new_id' );
		$this->time_callback        = is_callable( $time_callback ) ? $time_callback : array( $this, 'timestamp' );
	}

	public function prepare( array $finding, array $rename_plan, $baseline_snapshot_id, $current_schema_hash, $created_by ) {
		if ( ! $this->can_manage() || ! $this->is_eligible( $finding, $rename_plan ) || '' === (string) $current_schema_hash ) {
			return null;
		}

		$dry_run = $rename_plan['dry_run'];
		$field_key = isset( $finding['change']['after']['key'] ) ? (string) $finding['change']['after']['key'] : '';
		$plan    = new MigrationPlan(
			array(
				'id'                  => call_user_func( $this->id_callback ),
				'old_name'            => $rename_plan['old_name'],
				'new_name'            => $rename_plan['new_name'],
				'field_key'           => $field_key,
				'finding_fingerprint' => call_user_func( $this->fingerprint_callback, $finding ),
				'baseline_snapshot_id' => (string) $baseline_snapshot_id,
				'current_schema_hash' => (string) $current_schema_hash,
				'old_record_count'    => $dry_run['old_record_count'],
				'conflict_count'      => $dry_run['conflict_count'],
				'candidate_count'     => $dry_run['migration_candidate_count'],
				'status'              => MigrationPlan::DRAFT,
				'created_by'          => $created_by,
				'created_at'          => call_user_func( $this->time_callback ),
			)
		);

		return $this->repository->insert( $plan );
	}

	public function review( $plan_id, array $finding, $current_schema_hash, $reviewer_id, $review_note ) {
		if ( ! $this->can_manage() || '' === trim( (string) $review_note ) ) {
			return null;
		}

		$plan = $this->repository->find( $plan_id );
		if ( ! $plan || MigrationPlan::DRAFT !== $plan->status() ) {
			return null;
		}

		$fingerprint = call_user_func( $this->fingerprint_callback, $finding );
		if ( ! hash_equals( $plan->fingerprint(), (string) $fingerprint ) || ! hash_equals( $plan->schema_hash(), (string) $current_schema_hash ) ) {
			return $this->repository->save( $plan->with_invalid() );
		}

		$reviewed = $plan->with_review( $reviewer_id, $review_note, call_user_func( $this->time_callback ) );
		return $reviewed ? $this->repository->save( $reviewed ) : null;
	}

	public function latest_for( array $finding ) {
		return $this->repository->latest_for_fingerprint( call_user_func( $this->fingerprint_callback, $finding ) );
	}

	public function find( $plan_id ) { return $this->repository->find( $plan_id ); }

	private function can_manage() {
		return $this->capabilities->can( ProCapabilities::SAFE_RENAME_MIGRATIONS )->is_allowed();
	}

	private function is_eligible( array $finding, array $rename_plan ) {
		$change  = isset( $finding['change'] ) && is_array( $finding['change'] ) ? $finding['change'] : array();
		$dry_run = isset( $rename_plan['dry_run'] ) && is_array( $rename_plan['dry_run'] ) ? $rename_plan['dry_run'] : array();
		return 'modified' === ( isset( $change['kind'] ) ? $change['kind'] : '' )
			&& 'field' === ( isset( $change['node_type'] ) ? $change['node_type'] : '' )
			&& empty( $change['context']['ancestors'] )
			&& isset( $change['before']['name'], $change['after']['name'], $rename_plan['old_name'], $rename_plan['new_name'], $dry_run['old_record_count'], $dry_run['conflict_count'], $dry_run['migration_candidate_count'] )
			&& ! empty( $change['before']['key'] )
			&& ! empty( $change['after']['key'] )
			&& $change['before']['key'] === $change['after']['key']
			&& '' !== (string) $change['before']['name']
			&& '' !== (string) $change['after']['name']
			&& $change['before']['name'] !== $change['after']['name']
			&& $change['before']['name'] === $rename_plan['old_name']
			&& $change['after']['name'] === $rename_plan['new_name']
			&& 0 < (int) $dry_run['old_record_count'];
	}

	private function new_id() {
		return function_exists( 'wp_generate_uuid4' ) ? wp_generate_uuid4() : uniqid( '', true );
	}

	private function timestamp() {
		return gmdate( 'Y-m-d H:i:s' );
	}
}
