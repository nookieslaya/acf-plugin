<?php
namespace AcfSchemaGuard\Migrations;

if ( ! defined( 'ABSPATH' ) ) { exit; }

interface DirectMetaMigrationRepository {
	/** @return array[] Value-free current direct candidates. */
	public function discover( MigrationPlan $plan, $limit );
	/** @return array[] Value-free candidate results keyed by post ID. */
	public function inspect( MigrationPlan $plan, array $post_ids, $limit );

	/** @return MigrationBackupJournal|null Null means the record was revalidated and skipped. */
	public function copy( MigrationExecution $execution, MigrationPlan $plan, $post_id );
}
