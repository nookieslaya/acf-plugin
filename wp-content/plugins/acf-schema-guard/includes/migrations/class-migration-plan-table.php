<?php
/**
 * Installs the plugin-owned table for value-free migration plan metadata.
 *
 * @package ACFSchemaGuard
 */

namespace AcfSchemaGuard\Migrations;

use RuntimeException;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class MigrationPlanTable {
	const NAME = 'acf_schema_guard_migration_plans';

	public static function install( $wpdb ) {
		if ( ! function_exists( 'dbDelta' ) ) {
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		}

		if ( ! function_exists( 'dbDelta' ) ) {
			throw new RuntimeException( 'WordPress dbDelta is unavailable.' );
		}

		dbDelta( self::create_table_sql( $wpdb ) );

		$table_name = self::table_name( $wpdb );
		$installed  = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name ) );
		if ( $table_name !== $installed && false === $wpdb->query( self::create_table_sql( $wpdb ) ) ) {
			throw new RuntimeException( 'Migration plan table installation failed: ' . $wpdb->last_error );
		}
	}

	public static function table_name( $wpdb ) {
		return $wpdb->prefix . self::NAME;
	}

	public static function create_table_sql( $wpdb ) {
		$table_name      = self::table_name( $wpdb );
		$charset_collate = $wpdb->get_charset_collate();

		return "CREATE TABLE {$table_name} (
id char(36) NOT NULL,
old_name varchar(191) NOT NULL,
new_name varchar(191) NOT NULL,
field_key varchar(191) NOT NULL,
finding_fingerprint char(64) NOT NULL,
baseline_snapshot_id char(36) NOT NULL DEFAULT '',
current_schema_hash char(64) NOT NULL,
old_record_count bigint unsigned NOT NULL DEFAULT 0,
conflict_count bigint unsigned NOT NULL DEFAULT 0,
candidate_count bigint unsigned NOT NULL DEFAULT 0,
status varchar(20) NOT NULL,
created_by bigint unsigned NOT NULL,
created_at datetime NOT NULL,
reviewed_by bigint unsigned NOT NULL DEFAULT 0,
reviewed_note text NOT NULL,
reviewed_at datetime NULL,
PRIMARY KEY  (id),
KEY finding_status (finding_fingerprint, status),
KEY created_at (created_at)
) {$charset_collate};";
	}
}
