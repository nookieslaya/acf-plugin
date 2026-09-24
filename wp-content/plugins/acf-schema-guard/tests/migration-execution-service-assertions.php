<?php
define( 'ABSPATH', __DIR__ . '/' );
require_once dirname( __DIR__ ) . '/includes/licensing/class-pro-capabilities.php';
require_once dirname( __DIR__ ) . '/includes/licensing/class-license-state.php';
require_once dirname( __DIR__ ) . '/includes/licensing/class-capability-decision.php';
require_once dirname( __DIR__ ) . '/includes/licensing/class-capability-service.php';
require_once dirname( __DIR__ ) . '/includes/migrations/class-migration-plan.php';
require_once dirname( __DIR__ ) . '/includes/migrations/class-migration-execution.php';
require_once dirname( __DIR__ ) . '/includes/migrations/class-migration-backup-journal.php';
require_once dirname( __DIR__ ) . '/includes/migrations/interface-direct-meta-migration-repository.php';
require_once dirname( __DIR__ ) . '/includes/migrations/interface-migration-execution-audit-repository.php';
require_once dirname( __DIR__ ) . '/includes/migrations/class-migration-execution-service.php';

function asg_execution_assert( $condition, $message ) { if ( ! $condition ) { throw new RuntimeException( $message ); } }
$plan = new \AcfSchemaGuard\Migrations\MigrationPlan( array( 'id' => '11111111-1111-4111-8111-111111111111', 'old_name' => 'old_name', 'new_name' => 'new_name', 'field_key' => 'field_key', 'finding_fingerprint' => str_repeat( 'a', 64 ), 'baseline_snapshot_id' => '', 'current_schema_hash' => str_repeat( 'b', 64 ), 'old_record_count' => 2, 'conflict_count' => 0, 'candidate_count' => 2, 'status' => \AcfSchemaGuard\Migrations\MigrationPlan::REVIEWED, 'created_by' => 1, 'created_at' => '2026-09-24 12:00:00', 'reviewed_by' => 1, 'reviewed_note' => 'ok', 'reviewed_at' => '2026-09-24 12:01:00' ) );
$execution = new \AcfSchemaGuard\Migrations\MigrationExecution( array( 'id' => '22222222-2222-4222-8222-222222222222', 'plan_id' => $plan->id(), 'finding_fingerprint' => str_repeat( 'a', 64 ), 'current_schema_hash' => str_repeat( 'b', 64 ), 'status' => \AcfSchemaGuard\Migrations\MigrationExecution::RUNNING, 'requested_by' => 1, 'requested_at' => '2026-09-24 12:02:00', 'selected_count' => 1, 'copied_count' => 0, 'skipped_count' => 0, 'conflict_count' => 0, 'failed_count' => 0 ) );
$repository = new class() implements \AcfSchemaGuard\Migrations\DirectMetaMigrationRepository { public $copied = array(); public function discover( \AcfSchemaGuard\Migrations\MigrationPlan $plan, $limit ) { return array( array( 'post_id' => 1 ) ); } public function inspect( \AcfSchemaGuard\Migrations\MigrationPlan $plan, array $ids, $limit ) { return array_map( static function( $id ) { return array( 'post_id' => $id, 'status' => 2 === $id ? 'conflict' : 'eligible' ); }, $ids ); } public function copy( \AcfSchemaGuard\Migrations\MigrationExecution $execution, \AcfSchemaGuard\Migrations\MigrationPlan $plan, $post_id ) { $this->copied[] = $post_id; return null; } };
$audit = new class() implements \AcfSchemaGuard\Migrations\MigrationExecutionAuditRepository { public $started = 0; public $completed = 0; public $records = 0; public function start( \AcfSchemaGuard\Migrations\MigrationExecution $execution ) { $this->started++; return true; } public function complete( \AcfSchemaGuard\Migrations\MigrationExecution $execution ) { $this->completed++; return true; } public function record( \AcfSchemaGuard\Migrations\MigrationBackupJournal $journal ) { $this->records++; return true; } };
$service = new \AcfSchemaGuard\Migrations\MigrationExecutionService( $repository, new \AcfSchemaGuard\Licensing\CapabilityService( \AcfSchemaGuard\Licensing\LicenseState::valid( \AcfSchemaGuard\Licensing\ProCapabilities::all() ) ), $audit );
$service->execute( $execution, $plan, array( 1, 2 ) );
asg_execution_assert( array( 1 ) === $repository->copied, 'Only current eligible selected records may be copied.' );
asg_execution_assert( 1 === $audit->started, 'A permitted execution must create an audit record first.' );
asg_execution_assert( 1 === $audit->completed, 'A permitted execution must persist a terminal audit result.' );
asg_execution_assert( array() === $service->execute( $execution, $plan, range( 1, 21 ) ), 'Execution must reject selections above the bounded limit.' );
echo "Migration execution service assertions passed.\n";
