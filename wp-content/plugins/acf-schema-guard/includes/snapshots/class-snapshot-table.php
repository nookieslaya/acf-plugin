<?php
/**
 * Dedicated database table for immutable schema snapshots.
 *
 * @package ACFSchemaGuard
 */

namespace AcfSchemaGuard\Snapshots;

use RuntimeException;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Owns installation of the snapshot table.
 */
final class SnapshotTable {
	/** @var string */
	const NAME = 'acf_schema_guard_snapshots';

	/**
	 * Installs or upgrades the snapshot table during plugin activation.
	 *
	 * @param object $wpdb WordPress database adapter.
	 * @return void
	 */
	public static function install( $wpdb ) {
		if ( ! function_exists( 'dbDelta' ) ) {
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		}

		if ( ! function_exists( 'dbDelta' ) ) {
			throw new RuntimeException( 'WordPress dbDelta is unavailable.' );
		}

		dbDelta( self::create_table_sql( $wpdb ) );
	}

	/**
	 * Gets the prefixed snapshot table name.
	 *
	 * @param object $wpdb WordPress database adapter.
	 * @return string
	 */
	public static function table_name( $wpdb ) {
		return $wpdb->prefix . self::NAME;
	}

	/**
	 * Gets the table definition for dbDelta.
	 *
	 * @param object $wpdb WordPress database adapter.
	 * @return string
	 */
	public static function create_table_sql( $wpdb ) {
		$table_name      = self::table_name( $wpdb );
		$charset_collate = $wpdb->get_charset_collate();

		return "CREATE TABLE {$table_name} (
id char(36) NOT NULL,
source_id varchar(191) NOT NULL,
schema_version smallint unsigned NOT NULL,
schema longtext NOT NULL,
created_at datetime NOT NULL,
PRIMARY KEY  (id),
KEY source_created (source_id, created_at)
) {$charset_collate};";
	}
}
