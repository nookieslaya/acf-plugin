<?php
/**
 * Focused assertions for read-only direct field rename plans.
 *
 * @package ACFSchemaGuard
 */

define( 'ABSPATH', __DIR__ . '/' );

require_once dirname( __DIR__ ) . '/includes/impact/interface-rename-dry-run-repository.php';
require_once dirname( __DIR__ ) . '/includes/impact/class-safe-rename-planner.php';

function asg_safe_rename_assert( $condition, $message ) {
	if ( ! $condition ) {
		throw new RuntimeException( $message );
	}
}

$repository = new class() implements \AcfSchemaGuard\Impact\RenameDryRunRepository {
	public $calls = array();
	public function inspect( $old_name, $new_name, $limit ) {
		$this->calls[] = array( $old_name, $new_name, $limit );
		return array( 'old_record_count' => 4, 'conflict_count' => 1, 'records' => array( array( 'post_id' => 7, 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'Home' ) ) );
	}
};

$planner = new \AcfSchemaGuard\Impact\SafeRenamePlanner( $repository );
$change  = array( 'kind' => 'modified', 'node_type' => 'field', 'path' => array( 'group_hero', 'field_title' ), 'before' => array( 'name' => 'hero_title' ), 'after' => array( 'name' => 'hero_heading' ) );
$plans   = $planner->analyze( array( $change ), array( array( 'field_name' => 'hero_title', 'path' => 'hero.php', 'line' => 12, 'expression' => "get_field('hero_title')" ) ) );

asg_safe_rename_assert( 1 === count( $plans ), 'A direct field rename should create one plan.' );
asg_safe_rename_assert( 'hero_title' === $plans[0]['old_name'] && 'hero_heading' === $plans[0]['new_name'], 'The rename plan should preserve both field names.' );
asg_safe_rename_assert( 'conflicts' === $plans[0]['dry_run']['status'] && 3 === $plans[0]['dry_run']['migration_candidate_count'], 'Conflict dry-run arithmetic should be deterministic.' );
asg_safe_rename_assert( array( array( 'hero_title', 'hero_heading', 20 ) ) === $repository->calls, 'Direct rename planning should use one bounded repository call.' );
asg_safe_rename_assert( 1 === count( $plans[0]['code_references'] ), 'The rename plan should include only old-name literal references.' );

$nested = $change;
$nested['context'] = array( 'ancestors' => array( array( 'name' => 'rows', 'type' => 'repeater' ) ) );
$nested_plans = $planner->analyze( array( $nested ), array() );
asg_safe_rename_assert( 'not_supported' === $nested_plans[0]['dry_run']['status'] && 1 === count( $repository->calls ), 'Nested rename dry-runs must be explicit and must not query storage.' );

asg_safe_rename_assert( empty( $planner->analyze( array( array( 'kind' => 'modified', 'node_type' => 'field', 'before' => array( 'name' => 'same' ), 'after' => array( 'name' => 'same' ) ) ), array() ) ), 'Unchanged field names must not create rename plans.' );

$empty_new_name = $change;
$empty_new_name['after']['name'] = '';
asg_safe_rename_assert( empty( $planner->analyze( array( $empty_new_name ), array() ) ), 'An empty new field name must not create rename plans.' );

echo "Safe rename planner assertions passed.\n";
