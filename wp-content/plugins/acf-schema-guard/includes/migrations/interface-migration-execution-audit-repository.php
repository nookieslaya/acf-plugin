<?php
namespace AcfSchemaGuard\Migrations;
if ( ! defined( 'ABSPATH' ) ) { exit; }
interface MigrationExecutionAuditRepository {
	public function start( MigrationExecution $execution );
	public function complete( MigrationExecution $execution );
	public function record( MigrationBackupJournal $journal );
}
