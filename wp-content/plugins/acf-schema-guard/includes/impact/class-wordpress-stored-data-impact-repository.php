<?php
/**
 * Reads bounded direct post-meta impact evidence through WordPress' database API.
 *
 * @package ACFSchemaGuard
 */

namespace AcfSchemaGuard\Impact;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class WordPressStoredDataImpactRepository implements StoredDataImpactRepository {
	private $wpdb;

	public function __construct( $wpdb ) {
		$this->wpdb = $wpdb;
	}

	public function find( $field_name, $limit ) {
		$field_name = (string) $field_name;
		$limit      = max( 1, min( 20, (int) $limit ) );
		$meta_table = $this->wpdb->postmeta;
		$posts_table = $this->wpdb->posts;

		$count = $this->wpdb->get_var(
			$this->wpdb->prepare(
				"SELECT COUNT(DISTINCT post_id) FROM {$meta_table} WHERE meta_key = %s",
				$field_name
			)
		);
		$records = $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SELECT posts.ID AS post_id, posts.post_type, posts.post_status, posts.post_title FROM {$posts_table} AS posts INNER JOIN {$meta_table} AS meta ON meta.post_id = posts.ID WHERE meta.meta_key = %s GROUP BY posts.ID, posts.post_type, posts.post_status, posts.post_title ORDER BY posts.ID ASC LIMIT %d",
				$field_name,
				$limit
			),
			ARRAY_A
		);

		return array(
			'record_count' => max( 0, (int) $count ),
			'records'      => is_array( $records ) ? $this->records( $records ) : array(),
		);
	}

	private function records( array $records ) {
		$normalized = array();

		foreach ( $records as $record ) {
			if ( ! is_array( $record ) || ! isset( $record['post_id'] ) ) {
				continue;
			}
			$normalized[] = array(
				'post_id'     => (int) $record['post_id'],
				'post_type'   => isset( $record['post_type'] ) ? (string) $record['post_type'] : '',
				'post_status' => isset( $record['post_status'] ) ? (string) $record['post_status'] : '',
				'post_title'  => isset( $record['post_title'] ) ? (string) $record['post_title'] : '',
			);
		}

		return $normalized;
	}
}
