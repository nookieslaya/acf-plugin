<?php
/**
 * Isolated assertion for unavailable ACF source health.
 *
 * @package ACFSchemaGuard
 */

define( 'ABSPATH', __DIR__ . '/' );

require_once dirname( __DIR__ ) . '/includes/schema/class-canonical-value.php';
require_once dirname( __DIR__ ) . '/includes/schema/class-normalized-field.php';
require_once dirname( __DIR__ ) . '/includes/schema/class-normalized-field-group.php';
require_once dirname( __DIR__ ) . '/includes/schema/class-normalized-schema.php';
require_once dirname( __DIR__ ) . '/includes/schema/class-schema-normalizer.php';
require_once dirname( __DIR__ ) . '/includes/acf/class-source-health-finding.php';
require_once dirname( __DIR__ ) . '/includes/acf/class-source-health-report.php';
require_once dirname( __DIR__ ) . '/includes/acf/class-source-health-analyzer.php';
require_once dirname( __DIR__ ) . '/includes/acf/class-acf-source-health-provider.php';

$report = ( new \AcfSchemaGuard\Acf\AcfSourceHealthProvider() )->discover();

if ( $report->is_available() || array() !== $report->findings() ) {
	fwrite( STDERR, "Unavailable source health assertion failed.\n" );
	exit( 1 );
}

echo "Unavailable source health assertion passed.\n";
