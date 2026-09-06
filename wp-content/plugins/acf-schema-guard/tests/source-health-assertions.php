<?php
/**
 * Isolated assertions for Local JSON source-health classification.
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

$analyzer = new \AcfSchemaGuard\Acf\SourceHealthAnalyzer();
$report   = $analyzer->analyze(
	array(
		array( 'key' => 'group_aligned', 'title' => 'Aligned', 'settings' => array( 'beta' => 2, 'alpha' => 1 ) ),
		array( 'key' => 'group_database', 'title' => 'Database only' ),
		array( 'key' => 'group_divergent', 'title' => 'Database title', 'fields' => array( array( 'key' => 'field_a', 'type' => 'text' ) ) ),
	),
	array(
		array( 'title' => 'Aligned', 'settings' => array( 'alpha' => 1, 'beta' => 2 ), 'key' => 'group_aligned' ),
		array( 'key' => 'group_divergent', 'title' => 'JSON title', 'fields' => array( array( 'key' => 'field_a', 'type' => 'textarea' ) ) ),
		array( 'key' => 'group_json', 'title' => 'JSON only' ),
	)
);

$statuses = array();

foreach ( $report->findings() as $finding ) {
	$statuses[ $finding->field_group_key() ] = $finding->status();
}

$expected = array(
	'group_aligned'   => \AcfSchemaGuard\Acf\SourceHealthFinding::STATUS_ALIGNED,
	'group_database'  => \AcfSchemaGuard\Acf\SourceHealthFinding::STATUS_DATABASE_ONLY,
	'group_divergent' => \AcfSchemaGuard\Acf\SourceHealthFinding::STATUS_DIVERGENT,
	'group_json'      => \AcfSchemaGuard\Acf\SourceHealthFinding::STATUS_JSON_ONLY,
);

if ( ! $report->is_available() || $expected !== $statuses ) {
	fwrite( STDERR, "Source health classification assertion failed.\n" );
	exit( 1 );
}

$source_health_path = sys_get_temp_dir() . '/acf-schema-guard-source-health-' . uniqid( '', true );
mkdir( $source_health_path );
file_put_contents( $source_health_path . '/group_database.json', '{invalid json' );
file_put_contents(
	$source_health_path . '/group_json.json',
	json_encode(
		array(
			'key'    => 'group_json',
			'title'  => 'JSON only',
			'active' => true,
			'fields' => array(),
		)
	)
);

function get_posts( $args ) {
	return array( (object) array( 'ID' => 10 ) );
}

function acf_get_field_group( $id ) {
	return array(
		'key'    => 'group_database',
		'title'  => 'Database only',
		'active' => true,
	);
}

function acf_get_fields( $id ) {
	return array();
}

function acf_get_setting( $key ) {
	global $source_health_path;

	return 'load_json' === $key ? array( $source_health_path ) : null;
}

$source_health_path = $source_health_path;
$provider            = new \AcfSchemaGuard\Acf\AcfSourceHealthProvider();
$provider_report     = $provider->discover();
$provider_statuses   = array();

foreach ( $provider_report->findings() as $finding ) {
	$provider_statuses[ $finding->field_group_key() ] = $finding->status();
}

unlink( $source_health_path . '/group_database.json' );
unlink( $source_health_path . '/group_json.json' );
rmdir( $source_health_path );

if ( ! $provider_report->is_available() || array( 'group_database' => \AcfSchemaGuard\Acf\SourceHealthFinding::STATUS_DATABASE_ONLY, 'group_json' => \AcfSchemaGuard\Acf\SourceHealthFinding::STATUS_JSON_ONLY ) !== $provider_statuses ) {
	fwrite( STDERR, "Source health provider assertion failed.\n" );
	exit( 1 );
}

echo "Source health assertions passed.\n";
