<?php
/**
 * Focused assertions for bounded, read-only stored data impact analysis.
 *
 * @package ACFSchemaGuard
 */

define( 'ABSPATH', __DIR__ . '/' );
define( 'ARRAY_A', 'ARRAY_A' );

require_once dirname( __DIR__ ) . '/includes/impact/interface-stored-data-impact-repository.php';
require_once dirname( __DIR__ ) . '/includes/impact/class-wordpress-stored-data-impact-repository.php';
require_once dirname( __DIR__ ) . '/includes/impact/class-stored-data-impact.php';
require_once dirname( __DIR__ ) . '/includes/impact/class-stored-data-impact-analyzer.php';

function acf_schema_guard_stored_data_assert( $condition, $message ) {
	if ( ! $condition ) {
		throw new RuntimeException( $message );
	}
}

$repository = new class() implements \AcfSchemaGuard\Impact\StoredDataImpactRepository {
	public $calls = array();
	public function find( $field_name, $limit ) {
		$this->calls[] = array( $field_name, $limit );
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
acf_schema_guard_stored_data_assert( array( array( 'hero_title', 20 ), array( 'old_name', 20 ) ) === $repository->calls, 'Stored impact analysis must request the bounded record limit.' );

$group_impacts = $analyzer->analyze(
	array(
		array( 'kind' => 'removed', 'node_type' => 'field_group', 'before' => array( 'fields' => array( array( 'name' => 'hero_title' ), array( 'name' => 'hero_title' ) ) ), 'after' => null ),
	)
);
acf_schema_guard_stored_data_assert( 1 === count( $group_impacts ), 'Removed field groups should deduplicate their field names.' );

$wpdb = new class() {
	public $postmeta = 'wp_postmeta';
	public $posts = 'wp_posts';
	public $prepared = array();
	public function prepare( $query ) { $this->prepared[] = $query; return $query; }
	public function get_var( $query ) { return '3'; }
	public function get_results( $query, $format ) { return array( array( 'post_id' => 3, 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'About', 'meta_value' => 'must not escape' ) ); }
};
$wordpress_repository = new \AcfSchemaGuard\Impact\WordPressStoredDataImpactRepository( $wpdb );
$result = $wordpress_repository->find( 'hero_title', 999 );

acf_schema_guard_stored_data_assert( 3 === $result['record_count'] && 1 === count( $result['records'] ), 'WordPress repository should return count and safe records.' );
acf_schema_guard_stored_data_assert( ! isset( $result['records'][0]['meta_value'] ), 'WordPress repository must never return meta values.' );
acf_schema_guard_stored_data_assert( false !== strpos( $wpdb->prepared[0], 'meta_key = %s' ) && false !== strpos( $wpdb->prepared[1], 'LIMIT %d' ) && false === strpos( implode( ' ', $wpdb->prepared ), 'meta_value' ), 'WordPress repository must use exact meta keys, a bounded query, and no meta values.' );

echo "Stored data impact assertions passed.\n";
