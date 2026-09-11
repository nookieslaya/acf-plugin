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
require_once dirname( __DIR__ ) . '/includes/acf/class-schema-source-mode.php';
require_once dirname( __DIR__ ) . '/includes/acf/class-source-health-report.php';
require_once dirname( __DIR__ ) . '/includes/acf/class-source-health-analyzer.php';
require_once dirname( __DIR__ ) . '/includes/acf/class-field-group-descriptor.php';
require_once dirname( __DIR__ ) . '/includes/acf/class-acf-source-health-provider.php';

$analyzer = new \AcfSchemaGuard\Acf\SourceHealthAnalyzer();
$report   = $analyzer->analyze(
	array(
		array( 'key' => 'group_aligned', 'title' => 'Aligned', 'settings' => array( 'beta' => 2, 'alpha' => 1 ), '_source_modified' => 100 ),
		array( 'key' => 'group_database', 'title' => 'Database only' ),
		array( 'key' => 'group_divergent', 'title' => 'Database title', 'fields' => array( array( 'key' => 'field_a', 'type' => 'text' ) ), '_source_modified' => 100 ),
	),
	array(
		array( 'title' => 'Aligned', 'settings' => array( 'alpha' => 1, 'beta' => 2 ), 'key' => 'group_aligned', '_source_modified' => 200 ),
		array( 'key' => 'group_divergent', 'title' => 'JSON title', 'fields' => array( array( 'key' => 'field_a', 'type' => 'textarea' ) ), '_source_modified' => 50 ),
		array( 'key' => 'group_json', 'title' => 'JSON only' ),
	)
);

$statuses = array();
$directions = array();

foreach ( $report->findings() as $finding ) {
	$statuses[ $finding->field_group_key() ] = $finding->status();
	$directions[ $finding->field_group_key() ] = $finding->direction();
}

$expected = array(
	'group_aligned'   => \AcfSchemaGuard\Acf\SourceHealthFinding::STATUS_ALIGNED,
	'group_database'  => \AcfSchemaGuard\Acf\SourceHealthFinding::STATUS_DATABASE_ONLY,
	'group_divergent' => \AcfSchemaGuard\Acf\SourceHealthFinding::STATUS_DIVERGENT,
	'group_json'      => \AcfSchemaGuard\Acf\SourceHealthFinding::STATUS_JSON_ONLY,
);

if ( ! $report->is_available() || $expected !== $statuses || 'json_newer' !== $directions['group_aligned'] || 'database_newer' !== $directions['group_divergent'] || 'unknown' !== $directions['group_database'] ) {
	fwrite( STDERR, "Source health classification assertion failed.\n" );
	exit( 1 );
}

$runtime_defaults_report = $analyzer->analyze(
	array(
		array(
			'key'    => 'group_runtime_defaults',
			'title'  => 'Runtime defaults',
			'fields' => array(
				array(
					'key'           => 'field_image',
					'name'          => 'image',
					'type'          => 'image',
					'default_value' => null,
					'settings'      => array(
						'_valid'          => 1,
						'aria-label'      => '',
						'class'           => '',
						'parent_repeater' => 'field_parent',
						'wrapper'         => array( 'class' => '', 'id' => '', 'width' => '' ),
					),
				),
			),
		),
	),
	array(
		array(
			'key'    => 'group_runtime_defaults',
			'title'  => 'Runtime defaults',
			'fields' => array(
				array(
					'key'           => 'field_image',
					'name'          => 'image',
					'type'          => 'image',
					'default_value' => '',
					'settings'      => array(),
				),
			),
		),
	)
);

$runtime_defaults_finding = $runtime_defaults_report->findings()[0];
if ( \AcfSchemaGuard\Acf\SourceHealthFinding::STATUS_ALIGNED !== $runtime_defaults_finding->status() ) {
	fwrite( STDERR, "Source health runtime-default assertion failed.\n" );
	exit( 1 );
}

$meaningful_change_report = $analyzer->analyze(
	array( array( 'key' => 'group_meaningful_change', 'fields' => array( array( 'key' => 'field_a', 'name' => 'before', 'type' => 'text' ) ) ) ),
	array( array( 'key' => 'group_meaningful_change', 'fields' => array( array( 'key' => 'field_a', 'name' => 'after', 'type' => 'textarea' ) ) ) )
);

if ( \AcfSchemaGuard\Acf\SourceHealthFinding::STATUS_DIVERGENT !== $meaningful_change_report->findings()[0]->status() ) {
	fwrite( STDERR, "Source health meaningful-change assertion failed.\n" );
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
file_put_contents(
	$source_health_path . '/group_json__trashed.json',
	json_encode(
		array(
			'key'    => 'group_json__trashed',
			'title'  => 'Discarded JSON group',
			'active' => false,
			'fields' => array(),
		)
	)
);

function get_posts( $args ) {
	return array( (object) array( 'ID' => 10 ), (object) array( 'ID' => 11 ) );
}

function acf_get_field_group( $id ) {
	if ( 11 === $id ) {
		return array(
			'key'    => 'group_database__trashed',
			'title'  => 'Discarded database group',
			'active' => false,
		);
	}

	return array(
		'key'    => 'group_database',
		'title'  => 'Database only',
		'active' => true,
	);
}

function acf_get_fields( $id ) {
	return array();
}

function acf_get_raw_fields( $id ) {
	global $raw_field_parent_ids;

	$raw_field_parent_ids[] = (int) $id;

	return array();
}

function acf_get_setting( $key ) {
	global $source_health_path;

	return 'load_json' === $key ? array( $source_health_path ) : null;
}

$source_health_path = $source_health_path;
$raw_field_parent_ids = array();
$provider            = new \AcfSchemaGuard\Acf\AcfSourceHealthProvider();
$provider_report     = $provider->discover();
$provider_statuses   = array();

foreach ( $provider_report->findings() as $finding ) {
	$provider_statuses[ $finding->field_group_key() ] = $finding->status();
}

unlink( $source_health_path . '/group_database.json' );
unlink( $source_health_path . '/group_json.json' );
unlink( $source_health_path . '/group_json__trashed.json' );
rmdir( $source_health_path );

if ( ! $provider_report->is_available() || array( 10 ) !== $raw_field_parent_ids || array( 'group_database' => \AcfSchemaGuard\Acf\SourceHealthFinding::STATUS_DATABASE_ONLY, 'group_json' => \AcfSchemaGuard\Acf\SourceHealthFinding::STATUS_JSON_ONLY ) !== $provider_statuses ) {
	fwrite( STDERR, "Source health provider assertion failed.\n" );
	exit( 1 );
}

echo "Source health assertions passed.\n";
