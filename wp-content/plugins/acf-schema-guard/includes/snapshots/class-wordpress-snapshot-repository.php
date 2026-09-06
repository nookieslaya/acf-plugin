<?php
/**
 * WordPress database repository for immutable schema snapshots.
 *
 * @package ACFSchemaGuard
 */

namespace AcfSchemaGuard\Snapshots;

use RuntimeException;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Persists snapshots in the dedicated WordPress table.
 */
final class WordPressSnapshotRepository implements SnapshotRepository {
	/** @var int */
	const MAX_RECENT_SNAPSHOTS = 100;

	/** @var object */
	private $wpdb;

	/**
	 * @param object $wpdb WordPress database adapter.
	 */
	public function __construct( $wpdb ) {
		$this->wpdb = $wpdb;
	}

	/**
	 * Inserts one immutable snapshot.
	 *
	 * @param SchemaSnapshot $snapshot Snapshot to store.
	 * @return void
	 */
	public function insert( SchemaSnapshot $snapshot ) {
		$inserted = $this->wpdb->insert(
			SnapshotTable::table_name( $this->wpdb ),
			$snapshot->to_row(),
			array( '%s', '%s', '%d', '%s', '%s' )
		);

		if ( false === $inserted ) {
			$message = isset( $this->wpdb->last_error ) && '' !== $this->wpdb->last_error ? $this->wpdb->last_error : 'Unknown database error.';

			throw new RuntimeException( 'Snapshot insert failed: ' . $message );
		}
	}

	/**
	 * Finds one snapshot by ID.
	 *
	 * @param string $id Snapshot UUID.
	 * @return SchemaSnapshot|null
	 */
	public function find( $id ) {
		$sql = $this->wpdb->prepare(
			'SELECT id, source_id, schema_version, `schema`, created_at FROM ' . SnapshotTable::table_name( $this->wpdb ) . ' WHERE id = %s LIMIT 1',
			(string) $id
		);

		return $this->snapshot_from_query( $sql );
	}

	/**
	 * Finds the latest snapshot for one source.
	 *
	 * @param string $source_id Source identifier.
	 * @return SchemaSnapshot|null
	 */
	public function latest_for_source( $source_id ) {
		$source_id = $this->valid_source_id( $source_id );
		$sql       = $this->wpdb->prepare(
			'SELECT id, source_id, schema_version, `schema`, created_at FROM ' . SnapshotTable::table_name( $this->wpdb ) . ' WHERE source_id = %s ORDER BY created_at DESC, id DESC LIMIT 1',
			$source_id
		);

		return $this->snapshot_from_query( $sql );
	}

	/**
	 * Finds the newest stored snapshot.
	 *
	 * @return SchemaSnapshot|null
	 */
	public function latest() {
		$sql = 'SELECT id, source_id, schema_version, `schema`, created_at FROM ' . SnapshotTable::table_name( $this->wpdb ) . ' ORDER BY created_at DESC, id DESC LIMIT 1';

		return $this->snapshot_from_query( $sql );
	}

	/**
	 * Gets a bounded newest-first list of stored snapshots.
	 *
	 * @param int $limit Maximum number of snapshots.
	 * @return SchemaSnapshot[]
	 */
	public function recent( $limit ) {
		$limit = $this->valid_recent_limit( $limit );
		$sql   = $this->wpdb->prepare(
			'SELECT id, source_id, schema_version, `schema`, created_at FROM ' . SnapshotTable::table_name( $this->wpdb ) . ' ORDER BY created_at DESC, id DESC LIMIT %d',
			$limit
		);
		$rows  = $this->wpdb->get_results( $sql, ARRAY_A );

		if ( ! is_array( $rows ) ) {
			return array();
		}

		$snapshots = array();

		foreach ( $rows as $row ) {
			$snapshots[] = $this->snapshot_from_row( $row );
		}

		return $snapshots;
	}

	/**
	 * Gets all stored snapshots in deterministic newest-first order.
	 *
	 * @return SchemaSnapshot[]
	 */
	public function all() {
		$sql = 'SELECT id, source_id, schema_version, `schema`, created_at FROM ' . SnapshotTable::table_name( $this->wpdb ) . ' ORDER BY created_at DESC, id DESC';
		$rows = $this->wpdb->get_results( $sql, ARRAY_A );

		if ( ! is_array( $rows ) ) {
			return array();
		}

		$snapshots = array();

		foreach ( $rows as $row ) {
			$snapshots[] = $this->snapshot_from_row( $row );
		}

		return $snapshots;
	}

	/**
	 * @param string $sql Prepared query.
	 * @return SchemaSnapshot|null
	 */
	private function snapshot_from_query( $sql ) {
		$row = $this->wpdb->get_row( $sql, ARRAY_A );

		return is_array( $row ) ? $this->snapshot_from_row( $row ) : null;
	}

	/**
	 * @param array $row Stored snapshot row.
	 * @return SchemaSnapshot
	 */
	private function snapshot_from_row( array $row ) {
		return SchemaSnapshot::from_row( $row );
	}

	/**
	 * @param string $source_id Source identifier.
	 * @return string
	 */
	private function valid_source_id( $source_id ) {
		$source_id = (string) $source_id;

		if ( '' === trim( $source_id ) || 191 < strlen( $source_id ) ) {
			throw new RuntimeException( 'Snapshot source ID must contain at most 191 bytes.' );
		}

		return $source_id;
	}

	/**
	 * @param int $limit Requested number of snapshots.
	 * @return int
	 */
	private function valid_recent_limit( $limit ) {
		$limit = (int) $limit;

		if ( 1 > $limit || self::MAX_RECENT_SNAPSHOTS < $limit ) {
			throw new RuntimeException( 'Snapshot history limit must be between 1 and ' . self::MAX_RECENT_SNAPSHOTS . '.' );
		}

		return $limit;
	}
}
