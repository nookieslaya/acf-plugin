<?php
/**
 * Installs value-free execution and rollback-journal tables.
 *
 * @package ACFSchemaGuard
 */

namespace AcfSchemaGuard\Migrations;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class MigrationExecutionTable {
	const EXECUTIONS = 'acf_schema_guard_migration_executions';
	const JOURNAL    = 'acf_schema_guard_migration_backup_journal';

	public static function install( $wpdb ) {
		if ( ! function_exists( 'dbDelta' ) ) {
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		}
		dbDelta( self::execution_sql( $wpdb ) );
		dbDelta( self::journal_sql( $wpdb ) );
	}

	public static function executions_table( $wpdb ) { return $wpdb->prefix . self::EXECUTIONS; }
	public static function journal_table( $wpdb ) { return $wpdb->prefix . self::JOURNAL; }

	private static function execution_sql( $wpdb ) {
		$table = self::executions_table( $wpdb );
		return "CREATE TABLE {$table} (
id char(36) NOT NULL,
plan_id char(36) NOT NULL,
finding_fingerprint char(64) NOT NULL,
current_schema_hash char(64) NOT NULL,
status varchar(20) NOT NULL,
requested_by bigint unsigned NOT NULL,
requested_at datetime NOT NULL,
completed_at datetime NULL,
selected_count bigint unsigned NOT NULL DEFAULT 0,
copied_count bigint unsigned NOT NULL DEFAULT 0,
skipped_count bigint unsigned NOT NULL DEFAULT 0,
conflict_count bigint unsigned NOT NULL DEFAULT 0,
failed_count bigint unsigned NOT NULL DEFAULT 0,
PRIMARY KEY  (id),
KEY plan_status (plan_id, status)
) {$wpdb->get_charset_collate()};";
	}

	private static function journal_sql( $wpdb ) {
		$table = self::journal_table( $wpdb );
		return "CREATE TABLE {$table} (
id bigint unsigned NOT NULL AUTO_INCREMENT,
execution_id char(36) NOT NULL,
post_id bigint unsigned NOT NULL,
source_meta_id bigint unsigned NOT NULL,
source_key varchar(191) NOT NULL,
target_meta_id bigint unsigned NOT NULL,
target_key varchar(191) NOT NULL,
source_reference_meta_id bigint unsigned NOT NULL,
source_reference_key varchar(191) NOT NULL,
target_reference_meta_id bigint unsigned NOT NULL,
target_reference_key varchar(191) NOT NULL,
created_at datetime NOT NULL,
PRIMARY KEY  (id),
KEY execution_post (execution_id, post_id)
) {$wpdb->get_charset_collate()};";
	}
}
