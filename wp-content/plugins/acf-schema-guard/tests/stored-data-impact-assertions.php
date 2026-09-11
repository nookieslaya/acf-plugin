<?php
/**
 * Focused assertions for bounded, read-only stored data impact analysis.
 *
 * @package ACFSchemaGuard
 */

define( 'ABSPATH', __DIR__ . '/' );
define( 'ARRAY_A', 'ARRAY_A' );

require_once dirname( __DIR__ ) . '/includes/impact/interface-stored-data-impact-repository.php';
require_once dirname( __DIR__ ) . '/includes/impact/class-stored-data-impact-matcher.php';
require_once dirname( __DIR__ ) . '/includes/impact/class-stored-data-impact-matcher-factory.php';
require_once dirname( __DIR__ ) . '/includes/impact/class-wordpress-stored-data-impact-repository.php';
require_once dirname( __DIR__ ) . '/includes/impact/class-stored-data-impact.php';
require_once dirname( __DIR__ ) . '/includes/impact/class-stored-data-impact-analyzer.php';
require_once dirname( __DIR__ ) . '/includes/diff/class-schema-change.php';
require_once dirname( __DIR__ ) . '/includes/diff/class-schema-diff.php';
require_once dirname( __DIR__ ) . '/includes/diff/class-schema-differ.php';

function acf_schema_guard_stored_data_assert( $condition, $message ) {
	if ( ! $condition ) {
		throw new RuntimeException( $message );
	}
}

$repository = new class() implements \AcfSchemaGuard\Impact\StoredDataImpactRepository {
	public $calls = array();
	public function find( \AcfSchemaGuard\Impact\StoredDataImpactMatcher $matcher, $limit ) {
		$field_name = $matcher->field_name();
		$this->calls[] = array( $field_name, $limit, $matcher->to_array()['query_type'] );
		return array(
			'record_count' => 'hero_title' === $field_name ? 2 : 0,
			'records'      => 'hero_title' === $field_name ? array(
				array( 'post_id' => 17, 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'Home' ),
				array( 'post_id' => 24, 'post_type' => 'post', 'post_status' => 'draft', 'post_title' => 'Draft' ),
			) : array(),
		);
	}
};

$analyzer = new \AcfSchemaGuard\Impact\StoredDataImpactAnalyzer( $repository );
$changes  = array(
	array( 'kind' => 'removed', 'node_type' => 'field', 'before' => array( 'name' => 'hero_title' ), 'after' => null ),
	array( 'kind' => 'modified', 'node_type' => 'field', 'before' => array( 'name' => 'old_name' ), 'after' => array( 'name' => 'new_name' ) ),
	array( 'kind' => 'modified', 'node_type' => 'field', 'before' => array( 'name' => 'unchanged' ), 'after' => array( 'name' => 'unchanged' ) ),
);
$impacts = array_map( static function ( $impact ) { return $impact->to_array(); }, $analyzer->analyze( $changes ) );

acf_schema_guard_stored_data_assert( array( 'hero_title', 'old_name' ) === array_column( $impacts, 'field_name' ), 'Only removed and renamed fields should receive direct data impact analysis.' );
acf_schema_guard_stored_data_assert( 2 === $impacts[0]['record_count'] && 2 === count( $impacts[0]['records'] ), 'Stored impact should preserve safe record evidence.' );
acf_schema_guard_stored_data_assert( array( array( 'hero_title', 20, 'exact' ), array( 'old_name', 20, 'exact' ) ) === $repository->calls, 'Stored impact analysis must request the bounded record limit.' );
acf_schema_guard_stored_data_assert( 'direct' === $impacts[0]['matcher']['confidence'], 'Direct evidence should expose an explicit confidence contract.' );

$group_impacts = $analyzer->analyze(
	array(
		array( 'kind' => 'removed', 'node_type' => 'field_group', 'before' => array( 'fields' => array( array( 'name' => 'hero_title' ), array( 'name' => 'hero_title' ) ) ), 'after' => null ),
	)
);
acf_schema_guard_stored_data_assert( 1 === count( $group_impacts ), 'Removed field groups should deduplicate their field names.' );

$nested_before = array(
	'field_groups' => array(
		array(
			'key'    => 'group_features',
			'fields' => array(
				array(
					'key'        => 'field_features',
					'name'       => 'features',
					'type'       => 'repeater',
					'sub_fields' => array( array( 'key' => 'field_feature_title', 'name' => 'feature_title', 'type' => 'text' ) ),
				),
			),
		),
	),
);
$nested_after = $nested_before;
$nested_after['field_groups'][0]['fields'][0]['sub_fields'][0]['name'] = 'feature_heading';
$nested_changes = ( new \AcfSchemaGuard\Diff\SchemaDiffer() )->compare( $nested_before, $nested_after )->to_array()['changes'];
$nested_repository = new class() implements \AcfSchemaGuard\Impact\StoredDataImpactRepository {
	public $queries = array();
	public function find( \AcfSchemaGuard\Impact\StoredDataImpactMatcher $matcher, $limit ) {
		$this->queries[] = $matcher->to_array();
		return array( 'record_count' => 1, 'records' => array( array( 'post_id' => 99, 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'Nested fixture' ) ) );
	}
};
$nested_impacts = array_map( static function ( $impact ) { return $impact->to_array(); }, ( new \AcfSchemaGuard\Impact\StoredDataImpactAnalyzer( $nested_repository ) )->analyze( $nested_changes ) );
acf_schema_guard_stored_data_assert( 1 === count( $nested_impacts ) && 'features_{row}_feature_title' === $nested_impacts[0]['matcher']['pattern'] && 'nested' === $nested_impacts[0]['matcher']['confidence'], 'A real nested schema diff should retain its repeater ancestry through analysis.' );
acf_schema_guard_stored_data_assert( array( 'features_{row}_feature_title' ) === array_column( $nested_repository->queries, 'pattern' ), 'Nested analysis must query only the exact schema-derived matcher.' );

$unknown_repository = new class() implements \AcfSchemaGuard\Impact\StoredDataImpactRepository {
	public $calls = 0;
	public function find( \AcfSchemaGuard\Impact\StoredDataImpactMatcher $matcher, $limit ) { $this->calls++; return array(); }
};
$unknown_impacts = ( new \AcfSchemaGuard\Impact\StoredDataImpactAnalyzer( $unknown_repository ) )->analyze(
	array( array( 'kind' => 'removed', 'node_type' => 'field', 'before' => array( 'name' => 'clone_child' ), 'after' => null, 'context' => array( 'ancestors' => array( array( 'name' => 'cloned', 'type' => 'clone' ) ) ) ) )
);
acf_schema_guard_stored_data_assert( 1 === count( $unknown_impacts ) && 'unknown' === $unknown_impacts[0]->to_array()['matcher']['confidence'] && 0 === $unknown_repository->calls, 'Unsupported storage must remain explicit but must never run a guessed query.' );

$wpdb = new class() {
	public $postmeta = 'wp_postmeta';
	public $posts = 'wp_posts';
	public $prepared = array();
	public function prepare( $query ) { $this->prepared[] = $query; return $query; }
	public function get_var( $query ) { return '3'; }
	public function get_results( $query, $format ) { return array( array( 'post_id' => 3, 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'About', 'meta_value' => 'must not escape' ) ); }
};
$wordpress_repository = new \AcfSchemaGuard\Impact\WordPressStoredDataImpactRepository( $wpdb );
$result = $wordpress_repository->find( new \AcfSchemaGuard\Impact\StoredDataImpactMatcher( 'hero_title', array( 'hero_title' ), 'hero_title', 'exact', 'hero_title', 'direct' ), 999 );

acf_schema_guard_stored_data_assert( 3 === $result['record_count'] && 1 === count( $result['records'] ), 'WordPress repository should return count and safe records.' );
acf_schema_guard_stored_data_assert( ! isset( $result['records'][0]['meta_value'] ), 'WordPress repository must never return meta values.' );
acf_schema_guard_stored_data_assert( false !== strpos( $wpdb->prepared[0], 'meta_key = %s' ) && false !== strpos( $wpdb->prepared[1], 'LIMIT %d' ) && false === strpos( implode( ' ', $wpdb->prepared ), 'meta_value' ), 'WordPress repository must use exact meta keys, a bounded query, and no meta values.' );

$wpdb->prepared = array();
$wordpress_repository->find( new \AcfSchemaGuard\Impact\StoredDataImpactMatcher( 'feature_title', array( 'features', 'feature_title' ), 'features_{row}_feature_title', 'regexp', '^features_[0-9]+_feature_title$', 'nested' ), 20 );
acf_schema_guard_stored_data_assert( false !== strpos( $wpdb->prepared[0], 'meta_key REGEXP %s' ) && false !== strpos( $wpdb->prepared[1], 'meta_key REGEXP %s' ) && false === strpos( implode( ' ', $wpdb->prepared ), 'meta_value' ), 'Nested evidence must use a prepared numeric-row regular expression and no meta values.' );

echo "Stored data impact assertions passed.\n";
