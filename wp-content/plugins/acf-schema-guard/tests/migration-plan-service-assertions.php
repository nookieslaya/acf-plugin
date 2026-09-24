<?php
/**
 * Exercises eligibility and review without touching WordPress content data.
 *
 * @package ACFSchemaGuard
 */

define( 'ABSPATH', __DIR__ . '/' );

require_once dirname( __DIR__ ) . '/includes/licensing/class-pro-capabilities.php';
require_once dirname( __DIR__ ) . '/includes/licensing/class-license-state.php';
require_once dirname( __DIR__ ) . '/includes/licensing/class-capability-decision.php';
require_once dirname( __DIR__ ) . '/includes/licensing/class-capability-service.php';
require_once dirname( __DIR__ ) . '/includes/migrations/class-migration-plan.php';
require_once dirname( __DIR__ ) . '/includes/migrations/interface-migration-plan-repository.php';
require_once dirname( __DIR__ ) . '/includes/migrations/class-migration-plan-service.php';

function asg_migration_service_assert( $condition, $message ) {
	if ( ! $condition ) {
		throw new RuntimeException( $message );
	}
}

$repository = new class() implements \AcfSchemaGuard\Migrations\MigrationPlanRepository {
	public $plans = array();
	public function insert( \AcfSchemaGuard\Migrations\MigrationPlan $plan ) { $this->plans[ $plan->id() ] = $plan; return $plan; }
	public function find( $id ) { return isset( $this->plans[ $id ] ) ? $this->plans[ $id ] : null; }
	public function latest_for_fingerprint( $fingerprint ) { foreach ( array_reverse( $this->plans ) as $plan ) { if ( $plan->fingerprint() === $fingerprint ) { return $plan; } } return null; }
	public function save( \AcfSchemaGuard\Migrations\MigrationPlan $plan ) { $this->plans[ $plan->id() ] = $plan; return $plan; }
};

$capabilities = new \AcfSchemaGuard\Licensing\CapabilityService( \AcfSchemaGuard\Licensing\LicenseState::valid( array( \AcfSchemaGuard\Licensing\ProCapabilities::SAFE_RENAME_MIGRATIONS ) ) );
$counter      = 0;
$service      = new \AcfSchemaGuard\Migrations\MigrationPlanService(
	$repository,
	$capabilities,
	static function( $finding ) { return hash( 'sha256', serialize( $finding ) ); },
	static function() use ( &$counter ) { $counter++; return sprintf( '00000000-0000-4000-8000-%012d', $counter ); },
	static function() { return '2026-09-22 12:00:00'; }
);

$finding = array( 'change' => array( 'kind' => 'modified', 'node_type' => 'field', 'before' => array( 'name' => 'hero_title' ), 'after' => array( 'name' => 'hero_heading' ) ) );
$rename  = array( 'old_name' => 'hero_title', 'new_name' => 'hero_heading', 'dry_run' => array( 'old_record_count' => 3, 'conflict_count' => 1, 'migration_candidate_count' => 2 ) );
$hash    = str_repeat( 'a', 64 );
$plan    = $service->prepare( $finding, $rename, 'baseline-id', $hash, 12 );

asg_migration_service_assert( $plan && \AcfSchemaGuard\Migrations\MigrationPlan::DRAFT === $plan->status(), 'A direct rename with data should create a draft plan.' );
asg_migration_service_assert( 2 === $plan->to_array()['candidate_count'], 'The plan must retain bounded evidence counts.' );
asg_migration_service_assert( null === $service->review( $plan->id(), $finding, $hash, 13, '' ), 'A review note is required.' );
asg_migration_service_assert( \AcfSchemaGuard\Migrations\MigrationPlan::REVIEWED === $service->review( $plan->id(), $finding, $hash, 13, 'Checked on staging.' )->status(), 'A matching plan with a note should become reviewed.' );
asg_migration_service_assert( $plan->id() === $service->latest_for( $finding )->id(), 'The latest plan should be available by finding fingerprint.' );

$stale = $service->prepare( $finding, $rename, 'baseline-id', $hash, 12 );
asg_migration_service_assert( \AcfSchemaGuard\Migrations\MigrationPlan::INVALID === $service->review( $stale->id(), $finding, str_repeat( 'b', 64 ), 13, 'Schema changed.' )->status(), 'A stale schema hash must invalidate a draft plan.' );

$nested = $finding;
$nested['change']['context'] = array( 'ancestors' => array( array( 'type' => 'repeater' ) ) );
asg_migration_service_assert( null === $service->prepare( $nested, $rename, 'baseline-id', $hash, 12 ), 'Nested fields must not create migration plans.' );
$no_data = $rename;
$no_data['dry_run']['old_record_count'] = 0;
asg_migration_service_assert( null === $service->prepare( $finding, $no_data, 'baseline-id', $hash, 12 ), 'No-data evidence must not create migration plans.' );

$free_service = new \AcfSchemaGuard\Migrations\MigrationPlanService( $repository, new \AcfSchemaGuard\Licensing\CapabilityService( \AcfSchemaGuard\Licensing\LicenseState::valid( \AcfSchemaGuard\Licensing\ProCapabilities::free_validation() ) ), static function() { return 'unused'; } );
asg_migration_service_assert( null === $free_service->prepare( $finding, $rename, 'baseline-id', $hash, 12 ), 'Free entitlement must not create a migration plan.' );

echo "Migration plan service assertions passed.\n";
