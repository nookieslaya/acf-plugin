<?php
define( 'ABSPATH', __DIR__ . '/' );
function acf_get_setting( $key ) { return 'load_json' === $key ? array() : null; }
function get_posts() { return array(); }
function acf_get_field_group() { return array(); }
function acf_get_raw_fields() { return array(); }
require_once dirname( __DIR__ ) . '/includes/schema/class-canonical-value.php';
require_once dirname( __DIR__ ) . '/includes/schema/class-normalized-field.php';
require_once dirname( __DIR__ ) . '/includes/schema/class-normalized-field-group.php';
require_once dirname( __DIR__ ) . '/includes/schema/class-normalized-schema.php';
require_once dirname( __DIR__ ) . '/includes/schema/class-schema-normalizer.php';
require_once dirname( __DIR__ ) . '/includes/acf/class-field-group-descriptor.php';
require_once dirname( __DIR__ ) . '/includes/acf/class-source-health-finding.php';
require_once dirname( __DIR__ ) . '/includes/acf/class-schema-source-mode.php';
require_once dirname( __DIR__ ) . '/includes/acf/class-source-health-report.php';
require_once dirname( __DIR__ ) . '/includes/acf/class-source-health-analyzer.php';
require_once dirname( __DIR__ ) . '/includes/acf/class-acf-source-health-provider.php';
$report = ( new \AcfSchemaGuard\Acf\AcfSourceHealthProvider() )->discover();
if ( ! $report->is_available() || ! $report->source_mode()->is_database_first() || array() !== $report->findings() ) { throw new RuntimeException( 'Solo source mode assertion failed.' ); }
echo "Solo source mode assertions passed.\n";
