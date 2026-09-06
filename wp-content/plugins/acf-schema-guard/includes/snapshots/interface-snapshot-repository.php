<?php
/**
 * Append-only storage boundary for schema snapshots.
 *
 * @package ACFSchemaGuard
 */

namespace AcfSchemaGuard\Snapshots;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Stores and retrieves immutable schema snapshots.
 */
interface SnapshotRepository {
	/**
	 * Inserts one snapshot.
	 *
	 * @param SchemaSnapshot $snapshot Snapshot to store.
	 * @return void
	 */
	public function insert( SchemaSnapshot $snapshot );

	/**
	 * Finds one snapshot by ID.
	 *
	 * @param string $id Snapshot UUID.
	 * @return SchemaSnapshot|null
	 */
	public function find( $id );

	/**
	 * Finds the newest snapshot for one source.
	 *
	 * @param string $source_id Source identifier.
	 * @return SchemaSnapshot|null
	 */
	public function latest_for_source( $source_id );

	/**
	 * Finds the newest stored snapshot.
	 *
	 * @return SchemaSnapshot|null
	 */
	public function latest();

	/**
	 * Gets a bounded newest-first list of stored snapshots.
	 *
	 * @param int $limit Maximum number of snapshots.
	 * @return SchemaSnapshot[]
	 */
	public function recent( $limit );

	/**
	 * Gets all stored snapshots in deterministic newest-first order.
	 *
	 * @return SchemaSnapshot[]
	 */
	public function all();
}
