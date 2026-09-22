<?php
/**
 * Reads direct post-meta rename scope without loading field values.
 *
 * @package ACFSchemaGuard
 */

namespace AcfSchemaGuard\Impact;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class WordPressRenameDryRunRepository implements RenameDryRunRepository {
	private $wpdb;

	public function __construct( $wpdb ) {
		$this->wpdb = $wpdb;
	}

	public function inspect( $old_name, $new_name, $limit ) {
		$old_name   = (string) $old_name;
		$new_name   = (string) $new_name;
		$limit      = max( 1, min( 20, (int) $limit ) );
		$meta_table = $this->wpdb->postmeta;
		$posts_table = $this->wpdb->posts;
		$old_count  = $this->wpdb->get_var( $this->wpdb->prepare( "SELECT COUNT(DISTINCT post_id) FROM {$meta_table} WHERE meta_key = %s", $old_name ) );
		$conflicts  = $this->wpdb->get_var( $this->wpdb->prepare( "SELECT COUNT(DISTINCT old_meta.post_id) FROM {$meta_table} AS old_meta INNER JOIN {$meta_table} AS new_meta ON new_meta.post_id = old_meta.post_id AND new_meta.meta_key = %s WHERE old_meta.meta_key = %s", $new_name, $old_name ) );
		$records    = $this->wpdb->get_results(
			$this->wpdb->prepare( "SELECT posts.ID AS post_id, posts.post_type, posts.post_status, posts.post_title FROM {$posts_table} AS posts INNER JOIN {$meta_table} AS old_meta ON old_meta.post_id = posts.ID LEFT JOIN {$meta_table} AS new_meta ON new_meta.post_id = old_meta.post_id AND new_meta.meta_key = %s WHERE old_meta.meta_key = %s AND new_meta.post_id IS NULL GROUP BY posts.ID, posts.post_type, posts.post_status, posts.post_title ORDER BY posts.ID ASC LIMIT %d", $new_name, $old_name, $limit ),
			ARRAY_A
		);

		return array(
			'old_record_count' => max( 0, (int) $old_count ),
			'conflict_count'   => max( 0, (int) $conflicts ),
			'records'          => $this->records( is_array( $records ) ? $records : array() ),
		);
	}

	private function records( array $records ) {
		$normalized = array();
		foreach ( $records as $record ) {
			if ( ! is_array( $record ) || ! isset( $record['post_id'] ) ) {
				continue;
			}
			$normalized[] = array( 'post_id' => (int) $record['post_id'], 'post_type' => isset( $record['post_type'] ) ? (string) $record['post_type'] : '', 'post_status' => isset( $record['post_status'] ) ? (string) $record['post_status'] : '', 'post_title' => isset( $record['post_title'] ) ? (string) $record['post_title'] : '' );
		}
		return $normalized;
	}
}
