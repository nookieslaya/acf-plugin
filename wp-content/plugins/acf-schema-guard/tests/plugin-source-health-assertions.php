<?php
/**
 * Isolated assertion for the source-health composition boundary.
 *
 * @package ACFSchemaGuard
 */

define( 'ABSPATH', __DIR__ . '/' );
define( 'ACF_SCHEMA_GUARD_PATH', dirname( __DIR__ ) . '/' );

require_once dirname( __DIR__ ) . '/includes/class-plugin.php';

$report = \AcfSchemaGuard\Plugin::instance()->source_health();

if ( $report->is_available() || array() !== $report->findings() ) {
	fwrite( STDERR, "Plugin source health assertion failed.\n" );
	exit( 1 );
}

echo "Plugin source health assertion passed.\n";
