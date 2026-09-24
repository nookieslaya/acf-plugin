<?php
namespace AcfSchemaGuard\Migrations;

if ( ! defined( 'ABSPATH' ) ) { exit; }

final class WordPressDirectMetaMigrationRepository implements DirectMetaMigrationRepository {
	private $wpdb;
	private $table;

	public function __construct( $wpdb ) { $this->wpdb = $wpdb; $this->table = $wpdb->postmeta; }

	public function discover( MigrationPlan $plan, $limit ) {
		$data = $plan->to_array(); $limit = max( 1, min( 20, (int) $limit ));
		$sql = "SELECT DISTINCT source.post_id FROM {$this->table} source INNER JOIN {$this->table} reference ON reference.post_id = source.post_id AND reference.meta_key = %s AND reference.meta_value = %s LEFT JOIN {$this->table} target ON target.post_id = source.post_id AND target.meta_key IN (%s, %s) WHERE source.meta_key = %s AND target.meta_id IS NULL LIMIT %d";
		$ids = $this->wpdb->get_col( $this->wpdb->prepare( $sql, '_' . $data['old_name'], $data['field_key'], $data['new_name'], '_' . $data['new_name'], $data['old_name'], $limit ) );
		return array_map( static function( $id ) { return array( 'post_id' => (int) $id ); }, $ids );
	}

	public function inspect( MigrationPlan $plan, array $post_ids, $limit ) {
		$results = array();
		foreach ( array_slice( array_values( array_unique( array_filter( array_map( 'absint', $post_ids ) ) ) ), 0, max( 1, (int) $limit ) ) as $post_id ) {
			$results[ $post_id ] = array( 'post_id' => $post_id, 'status' => $this->source_row( $plan, $post_id, false ) ? ( $this->target_exists( $plan, $post_id ) ? 'conflict' : 'eligible' ) : 'unavailable' );
		}
		return $results;
	}

	public function copy( MigrationExecution $execution, MigrationPlan $plan, $post_id ) {
		$source = $this->source_row( $plan, $post_id, true );
		if ( ! $source || $this->target_exists( $plan, $post_id ) ) { return null; }
		if ( false === $this->wpdb->insert( $this->table, array( 'post_id' => $post_id, 'meta_key' => $plan->to_array()['new_name'], 'meta_value' => $source['meta_value'] ) ) ) { return null; }
		$target_meta_id = (int) $this->wpdb->insert_id;
		if ( false === $this->wpdb->insert( $this->table, array( 'post_id' => $post_id, 'meta_key' => '_' . $plan->to_array()['new_name'], 'meta_value' => $plan->to_array()['field_key'] ) ) ) {
			$this->wpdb->delete( $this->table, array( 'meta_id' => $target_meta_id ), array( '%d' ) );
			return null;
		}
		return new MigrationBackupJournal( array( 'execution_id' => $execution->to_array()['id'], 'post_id' => $post_id, 'source_meta_id' => $source['meta_id'], 'source_key' => $plan->to_array()['old_name'], 'target_meta_id' => $target_meta_id, 'target_key' => $plan->to_array()['new_name'], 'source_reference_meta_id' => $source['reference_meta_id'], 'source_reference_key' => '_' . $plan->to_array()['old_name'], 'target_reference_meta_id' => (int) $this->wpdb->insert_id, 'target_reference_key' => '_' . $plan->to_array()['new_name'], 'created_at' => gmdate( 'Y-m-d H:i:s' ) ) );
	}

	private function source_row( MigrationPlan $plan, $post_id, $include_value ) {
		$data = $plan->to_array();
		$columns = $include_value ? 'source.meta_id, source.meta_value, reference.meta_id AS reference_meta_id' : 'source.meta_id';
		$sql = "SELECT {$columns} FROM {$this->table} source INNER JOIN {$this->table} reference ON reference.post_id = source.post_id AND reference.meta_key = %s AND reference.meta_value = %s WHERE source.post_id = %d AND source.meta_key = %s LIMIT 1";
		return $this->wpdb->get_row( $this->wpdb->prepare( $sql, '_' . $data['old_name'], $data['field_key'], $post_id, $data['old_name'] ), ARRAY_A );
	}

	private function target_exists( MigrationPlan $plan, $post_id ) {
		$name = $plan->to_array()['new_name'];
		return 0 < (int) $this->wpdb->get_var( $this->wpdb->prepare( "SELECT COUNT(*) FROM {$this->table} WHERE post_id = %d AND meta_key IN (%s, %s)", $post_id, $name, '_' . $name ) );
	}
}
