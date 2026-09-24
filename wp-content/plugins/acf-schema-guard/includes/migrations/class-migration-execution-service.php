<?php
namespace AcfSchemaGuard\Migrations;

use AcfSchemaGuard\Licensing\CapabilityService;
use AcfSchemaGuard\Licensing\ProCapabilities;

if ( ! defined( 'ABSPATH' ) ) { exit; }

final class MigrationExecutionService {
	const LIMIT = 20;
	private $repository;
	private $capabilities;
	private $audit;

	public function __construct( DirectMetaMigrationRepository $repository, CapabilityService $capabilities, ?MigrationExecutionAuditRepository $audit = null ) { $this->repository = $repository; $this->capabilities = $capabilities; $this->audit = $audit; }

	public function inspect( MigrationPlan $plan, array $post_ids ) {
		if ( ! $this->can_execute( $plan ) || count( array_unique( $post_ids ) ) > self::LIMIT ) { return array(); }
		return $this->repository->inspect( $plan, $post_ids, self::LIMIT );
	}

	public function candidates( MigrationPlan $plan ) { return $this->can_execute( $plan ) ? $this->repository->discover( $plan, self::LIMIT ) : array(); }

	public function execute( MigrationExecution $execution, MigrationPlan $plan, array $post_ids ) {
		if ( ! $this->can_execute( $plan ) || MigrationExecution::RUNNING !== $execution->to_array()['status'] || empty( $post_ids ) || count( array_unique( $post_ids ) ) > self::LIMIT ) { return array(); }
		if ( $this->audit && ! $this->audit->start( $execution ) ) { return array(); }
		$results = array(); $copied = 0; $skipped = 0; $conflicts = 0; $failed = 0;
		foreach ( $this->repository->inspect( $plan, $post_ids, self::LIMIT ) as $candidate ) {
			if ( 'conflict' === $candidate['status'] ) { $conflicts++; $results[] = null; continue; }
			if ( 'eligible' !== $candidate['status'] ) { $skipped++; $results[] = null; continue; }
			$result = $this->repository->copy( $execution, $plan, $candidate['post_id'] );
			$result ? $copied++ : $failed++;
			if ( $result && $this->audit ) { $this->audit->record( $result ); }
			$results[] = $result;
		}
		if ( $this->audit ) { $this->audit->complete( $execution->with_results( $copied, $skipped, $conflicts, $failed, gmdate( 'Y-m-d H:i:s' ) ) ); }
		return $results;
	}

	private function can_execute( MigrationPlan $plan ) {
		return $this->capabilities->can( ProCapabilities::SAFE_RENAME_MIGRATIONS )->is_allowed() && MigrationPlan::REVIEWED === $plan->status();
	}
}
