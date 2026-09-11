<?php
/**
 * Focused assertions for conservative nested ACF post-meta matcher derivation.
 *
 * @package ACFSchemaGuard
 */

define( 'ABSPATH', __DIR__ . '/' );

require_once dirname( __DIR__ ) . '/includes/impact/class-stored-data-impact-matcher.php';
require_once dirname( __DIR__ ) . '/includes/impact/class-stored-data-impact-matcher-factory.php';

function acf_schema_guard_matcher_assert( $condition, $message ) {
	if ( ! $condition ) {
		throw new RuntimeException( $message );
	}
}

$factory = new \AcfSchemaGuard\Impact\StoredDataImpactMatcherFactory();

$direct = $factory->for_change(
	array(
		'kind'      => 'modified',
		'node_type' => 'field',
		'before'    => array( 'name' => 'hero_title' ),
		'after'     => array( 'name' => 'hero_heading' ),
	)
);
acf_schema_guard_matcher_assert( 1 === count( $direct ) && array( 'field_name' => 'hero_title', 'field_path' => array( 'hero_title' ), 'pattern' => 'hero_title', 'query_type' => 'exact', 'confidence' => 'direct' ) === $direct[0]->to_array(), 'Direct field rename should remain an exact matcher.' );

$group = $factory->for_change(
	array(
		'kind'      => 'removed',
		'node_type' => 'field',
		'before'    => array( 'name' => 'feature_badge' ),
		'after'     => null,
		'context'   => array( 'ancestors' => array( array( 'name' => 'feature_meta', 'type' => 'group' ) ) ),
	)
);
acf_schema_guard_matcher_assert( 'feature_meta_feature_badge' === $group[0]->to_array()['pattern'] && 'nested' === $group[0]->to_array()['confidence'] && '^feature_meta_feature_badge$' === $group[0]->query_value(), 'Group children should use one exact schema-derived nested pattern.' );

$repeater = $factory->for_change(
	array(
		'kind'      => 'removed',
		'node_type' => 'field',
		'before'    => array( 'name' => 'feature_title' ),
		'after'     => null,
		'context'   => array( 'ancestors' => array( array( 'name' => 'features', 'type' => 'repeater' ) ) ),
	)
);
acf_schema_guard_matcher_assert( 'features_{row}_feature_title' === $repeater[0]->to_array()['pattern'] && '^features_[0-9]+_feature_title$' === $repeater[0]->query_value(), 'Repeater children must match numeric rows only.' );

$flexible = $factory->for_change(
	array(
		'kind'      => 'removed',
		'node_type' => 'field',
		'before'    => array( 'name' => 'card_title' ),
		'after'     => null,
		'context'   => array( 'ancestors' => array( array( 'name' => 'page_sections', 'type' => 'flexible_content' ), array( 'name' => 'cards', 'type' => 'repeater' ) ) ),
	)
);
acf_schema_guard_matcher_assert( 'page_sections_{row}_cards_{row}_card_title' === $flexible[0]->to_array()['pattern'] && '^page_sections_[0-9]+_cards_[0-9]+_card_title$' === $flexible[0]->query_value(), 'Flexible Content and nested repeater children must retain both numeric row boundaries.' );

$unknown = $factory->for_change(
	array(
		'kind'      => 'removed',
		'node_type' => 'field',
		'before'    => array( 'name' => 'unsafe_child' ),
		'after'     => null,
		'context'   => array( 'ancestors' => array( array( 'name' => 'clone_source', 'type' => 'clone' ) ) ),
	)
);
acf_schema_guard_matcher_assert( 'unknown' === $unknown[0]->to_array()['confidence'] && 'none' === $unknown[0]->query_type(), 'Unsupported ACF ancestors must not create a queryable matcher.' );

$removed_group = $factory->for_change(
	array(
		'kind'      => 'removed',
		'node_type' => 'field_group',
		'before'    => array(
			'fields' => array(
				array( 'name' => 'features', 'type' => 'repeater', 'sub_fields' => array( array( 'name' => 'feature_title', 'type' => 'text' ) ) ),
			),
		),
		'after'     => null,
	)
);
acf_schema_guard_matcher_assert( array( 'features', 'feature_title' ) === array_column( array_map( static function ( $matcher ) { return $matcher->to_array(); }, $removed_group ), 'field_name' ), 'Removed groups should include direct parents and their nested child evidence.' );

echo "Stored data impact matcher assertions passed.\n";
