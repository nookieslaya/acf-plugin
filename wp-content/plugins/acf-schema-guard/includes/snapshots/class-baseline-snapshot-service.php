<?php
/**
 * Stores and resolves the administrator-approved schema baseline.
 *
 * @package ACFSchemaGuard
 */

namespace AcfSchemaGuard\Snapshots;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class BaselineSnapshotService {
	const OPTION_NAME = 'acf_schema_guard_baseline_snapshot_id';

	/** @var SnapshotRepository */
	private $snapshots;

	/**
	 * @param SnapshotRepository $snapshots Stored snapshots.
	 */
	public function __construct( SnapshotRepository $snapshots ) {
		$this->snapshots = $snapshots;
	}

	/**
	 * Marks one stored snapshot as the approved baseline.
	 *
	 * @param SchemaSnapshot $snapshot Approved snapshot.
	 * @return void
	 */
	public function set( SchemaSnapshot $snapshot ) {
		update_option( self::OPTION_NAME, $snapshot->id(), false );
	}

	/**
	 * Gets the usable baseline snapshot, if one has been approved.
	 *
	 * @return SchemaSnapshot|null
	 */
	public function snapshot() {
		$id = get_option( self::OPTION_NAME, '' );

		if ( ! is_string( $id ) || 1 !== preg_match( '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $id ) ) {
			return null;
		}

		return $this->snapshots->find( $id );
	}
}
