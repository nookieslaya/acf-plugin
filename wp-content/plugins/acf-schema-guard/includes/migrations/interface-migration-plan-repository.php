<?php
/**
 * Persistence boundary for plugin-owned migration plan metadata.
 *
 * @package ACFSchemaGuard
 */

namespace AcfSchemaGuard\Migrations;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

interface MigrationPlanRepository {
	public function insert( MigrationPlan $plan );

	public function find( $id );

	public function latest_for_fingerprint( $fingerprint );

	public function save( MigrationPlan $plan );
}
