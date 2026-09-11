<?php
/**
 * Validates the isolated Local JSON fixture for nested data-impact manual tests.
 *
 * @package ACFSchemaGuard
 */

$fixture_path = dirname( __DIR__, 4 ) . '/wp-content/themes/acf-schema-guard-dev/acf-json/group_acf_schema_guard_impact_fixture.json';
$fixture      = json_decode( file_get_contents( $fixture_path ), true );

function acf_schema_guard_nested_fixture_assert( $condition, $message ) {
	if ( ! $condition ) {
		throw new RuntimeException( $message );
	}
}

function acf_schema_guard_nested_fixture_field( array $fields, $key ) {
	foreach ( $fields as $field ) {
		if ( is_array( $field ) && isset( $field['key'] ) && $key === $field['key'] ) {
			return $field;
		}
	}

	return array();
}

acf_schema_guard_nested_fixture_assert( is_array( $fixture ) && 'group_acf_schema_guard_impact_fixture' === $fixture['key'], 'Nested-impact fixture must be valid isolated Local JSON.' );
acf_schema_guard_nested_fixture_assert( false === $fixture['active'], 'Nested-impact fixture must remain inactive until a manual test starts.' );

$direct   = acf_schema_guard_nested_fixture_field( $fixture['fields'], 'field_acf_schema_guard_impact_fixture_direct' );
$group    = acf_schema_guard_nested_fixture_field( $fixture['fields'], 'field_acf_schema_guard_impact_fixture_group' );
$repeater = acf_schema_guard_nested_fixture_field( $fixture['fields'], 'field_acf_schema_guard_impact_fixture_repeater' );
$flexible = acf_schema_guard_nested_fixture_field( $fixture['fields'], 'field_acf_schema_guard_impact_fixture_flexible' );

acf_schema_guard_nested_fixture_assert( 'text' === $direct['type'], 'Fixture must include one direct field.' );
acf_schema_guard_nested_fixture_assert( 'group' === $group['type'] && ! empty( $group['sub_fields'] ), 'Fixture must include one Group child case.' );
acf_schema_guard_nested_fixture_assert( 'repeater' === $repeater['type'] && ! empty( $repeater['sub_fields'] ), 'Fixture must include one Repeater child case.' );
acf_schema_guard_nested_fixture_assert( 'flexible_content' === $flexible['type'] && ! empty( $flexible['layouts'] ), 'Fixture must include one Flexible Content case.' );

$layout = reset( $flexible['layouts'] );
acf_schema_guard_nested_fixture_assert( is_array( $layout ) && ! empty( $layout['sub_fields'] ), 'Fixture Flexible Content layout must have child fields.' );
$nested_repeater = acf_schema_guard_nested_fixture_field( $layout['sub_fields'], 'field_acf_schema_guard_impact_fixture_flexible_repeater' );
acf_schema_guard_nested_fixture_assert( 'repeater' === $nested_repeater['type'] && ! empty( $nested_repeater['sub_fields'] ), 'Fixture must include a Repeater inside Flexible Content.' );

echo "Nested impact fixture assertions passed.\n";
