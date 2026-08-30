<?php
/**
 * Isolated assertions for ACF schema normalization.
 *
 * @package ACFSchemaGuard
 */

define( 'ABSPATH', __DIR__ . '/' );
define( 'ACF_SCHEMA_GUARD_PATH', dirname( __DIR__ ) . '/' );

function acf_get_field_groups() {
	return array(
		array(
			'key'      => 'group_b',
			'title'    => 'B',
			'active'   => true,
			'location' => array(
				array(
					array( 'param' => 'post_type', 'operator' => '==', 'value' => 'post' ),
					array( 'param' => 'post_type', 'operator' => '==', 'value' => 'page' ),
				),
			),
		),
		array(
			'key'    => 'group_a',
			'title'  => 'A',
			'active' => true,
		),
	);
}

function acf_get_fields( $field_group ) {
	if ( 'group_a' === $field_group['key'] ) {
		return array();
	}

	return array(
		array(
			'key'        => 'field_repeater',
			'name'       => 'items',
			'type'       => 'repeater',
			'ID'         => 123,
			'parent'     => 456,
			'prefix'     => 'acf',
			'value'      => 'runtime value',
			'menu_order' => 3,
			'custom'     => array( 'zebra' => 'z', 'alpha' => 'a' ),
			'sub_fields' => array(
				array( 'key' => 'field_second', 'name' => 'second', 'type' => 'text' ),
				array( 'key' => 'field_first', 'name' => 'first', 'type' => 'text' ),
			),
		),
		array(
			'key'     => 'field_flexible',
			'name'    => 'blocks',
			'type'    => 'flexible_content',
			'layouts' => array(
				array( 'key' => 'layout_z', 'name' => 'z', 'sub_fields' => array() ),
				array( 'key' => 'layout_a', 'name' => 'a', 'sub_fields' => array() ),
			),
		),
	);
}

require_once dirname( __DIR__ ) . '/includes/class-plugin.php';

$schema = \AcfSchemaGuard\Plugin::instance()->normalized_schema()->to_array();

$group_b  = $schema['field_groups'][1];
$flexible = $group_b['fields'][0];
$repeater = $group_b['fields'][1];

if (
	1 !== $schema['schema_version'] ||
	'group_a' !== $schema['field_groups'][0]['key'] ||
	'page' !== $group_b['location'][0][0]['value'] ||
	array( 'alpha', 'zebra' ) !== array_keys( $repeater['settings']['custom'] ) ||
	isset( $repeater['settings']['ID'] ) ||
	'field_second' !== $repeater['sub_fields'][0]['key'] ||
	'layout_a' !== $flexible['layouts'][0]['key']
) {
	fwrite( STDERR, "Normalized schema assertion failed.\n" );
	exit( 1 );
}

echo "Normalized schema assertions passed.\n";
