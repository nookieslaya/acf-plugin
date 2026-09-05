<?php
/**
 * Isolated assertions for core schema change explanations.
 *
 * @package ACFSchemaGuard
 */

define( 'ABSPATH', __DIR__ . '/' );

require_once dirname( __DIR__ ) . '/includes/diff/class-schema-change-explainer.php';

function acf_schema_guard_explainer_assert( $condition, $message ) {
	if ( ! $condition ) {
		throw new RuntimeException( $message );
	}
}

$explainer = new \AcfSchemaGuard\Diff\SchemaChangeExplainer();
$modified  = $explainer->explain(
	array(
		'kind'      => 'modified',
		'node_type' => 'field',
		'before'    => array(
			'name'          => 'hero_text',
			'label'         => 'Hero text',
			'type'          => 'textarea',
			'required'      => false,
			'instructions'  => '',
			'default_value' => null,
		),
		'after'     => array(
			'name'          => 'hero_text_new',
			'label'         => 'Hero content',
			'type'          => 'text',
			'required'      => true,
			'instructions'  => 'Short text only.',
			'default_value' => array( 'copy' => 'Welcome' ),
		),
	)
);

acf_schema_guard_explainer_assert( 'Field modified.' === $modified['summary'], 'Modified field summary is wrong.' );
acf_schema_guard_explainer_assert(
	array(
		'Field name: "hero_text" -> "hero_text_new"',
		'Field label: "Hero text" -> "Hero content"',
		'Field type: "textarea" -> "text"',
		'Required: no -> yes',
		'Instructions: (empty) -> "Short text only."',
		'Default value: (none) -> {"copy":"Welcome"}',
	) === $modified['details'],
	'Modified field details are wrong or unordered.'
);

$group = $explainer->explain(
	array(
		'kind'      => 'modified',
		'node_type' => 'field_group',
		'before'    => array( 'title' => 'Hero', 'active' => true ),
		'after'     => array( 'title' => 'Page hero', 'active' => false ),
	)
);
acf_schema_guard_explainer_assert( array( 'Group title: "Hero" -> "Page hero"', 'Active: yes -> no' ) === $group['details'], 'Modified group details are wrong.' );

$unchanged = $explainer->explain(
	array(
		'kind'      => 'modified',
		'node_type' => 'field',
		'before'    => array( 'name' => 'hero_title', 'type' => 'text' ),
		'after'     => array( 'name' => 'hero_title', 'type' => 'text' ),
	)
);
acf_schema_guard_explainer_assert( empty( $unchanged['details'] ), 'Equal properties should not be described.' );

$added = $explainer->explain(
	array(
		'kind'      => 'added',
		'node_type' => 'field',
		'before'    => null,
		'after'     => array( 'label' => 'Hero text', 'name' => 'hero_text', 'type' => 'textarea' ),
	)
);
acf_schema_guard_explainer_assert( 'Field added.' === $added['summary'] && 'Field: "Hero text" ("hero_text"), type "textarea".' === $added['details'][0], 'Added field explanation is wrong.' );

$removed = $explainer->explain(
	array(
		'kind'      => 'removed',
		'node_type' => 'field_group',
		'before'    => array( 'title' => 'Hero' ),
		'after'     => null,
	)
);
acf_schema_guard_explainer_assert( 'Field group removed.' === $removed['summary'] && 'Field group: "Hero".' === $removed['details'][0], 'Removed group explanation is wrong.' );

$rules = $explainer->explain(
	array(
		'kind'      => 'modified',
		'node_type' => 'field_group',
		'before'    => array( 'location' => array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'page' ) ) ) ),
		'after'     => array( 'location' => array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'post' ) ) ) ),
	)
);
acf_schema_guard_explainer_assert( 1 === count( $rules['details'] ) && 0 === strpos( $rules['details'][0], 'Location rules:' ), 'Location rule explanation is wrong.' );

$settings = $explainer->explain(
	array(
		'kind'      => 'modified',
		'node_type' => 'field',
		'before'    => array(
			'conditional_logic' => array( array( array( 'field' => 'field_enabled', 'operator' => '==', 'value' => '1' ) ) ),
			'settings'          => array( 'return_format' => 'array', 'rows' => 4 ),
		),
		'after'     => array(
			'conditional_logic' => array(),
			'settings'          => array( 'allow_null' => true, 'return_format' => 'id' ),
		),
	)
);
acf_schema_guard_explainer_assert(
	array(
		'Conditional logic: [[{"field":"field_enabled","operator":"==","value":"1"}]] -> []',
		'Setting "allow_null" added: yes',
		'Setting "return_format": "array" -> "id"',
		'Setting "rows" removed: 4',
	) === $settings['details'],
	'Conditional logic or setting details are wrong.'
);

$nested_modified = $explainer->explain(
	array(
		'kind'      => 'modified',
		'node_type' => 'field',
		'path'      => array( 'group_content', 'field_rows', 'field_heading' ),
		'before'    => array( 'name' => 'heading', 'type' => 'text' ),
		'after'     => array( 'name' => 'heading', 'type' => 'textarea' ),
	)
);
acf_schema_guard_explainer_assert( 'Nested field modified.' === $nested_modified['summary'], 'Nested modified field summary is wrong.' );
acf_schema_guard_explainer_assert( array( 'Field type: "text" -> "textarea"' ) === $nested_modified['details'], 'Nested modified field details are wrong.' );

$deeply_nested_added = $explainer->explain(
	array(
		'kind'      => 'added',
		'node_type' => 'field',
		'path'      => array( 'group_content', 'field_sections', 'layout_hero', 'field_cta', 'field_url' ),
		'before'    => null,
		'after'     => array( 'label' => 'URL', 'name' => 'url', 'type' => 'url' ),
	)
);
acf_schema_guard_explainer_assert( 'Nested field added.' === $deeply_nested_added['summary'], 'Deeply nested field summary is wrong.' );
acf_schema_guard_explainer_assert( 'Nested field: "URL" ("url"), type "url".' === $deeply_nested_added['details'][0], 'Deeply nested field description is wrong.' );

$top_level_with_path = $explainer->explain(
	array(
		'kind'      => 'removed',
		'node_type' => 'field',
		'path'      => array( 'group_content', 'field_title' ),
		'before'    => array( 'label' => 'Title', 'name' => 'title', 'type' => 'text' ),
		'after'     => null,
	)
);
acf_schema_guard_explainer_assert( 'Field removed.' === $top_level_with_path['summary'], 'Top-level field summary changed unexpectedly.' );
acf_schema_guard_explainer_assert( 'Field: "Title" ("title"), type "text".' === $top_level_with_path['details'][0], 'Top-level field description changed unexpectedly.' );

$layouts = $explainer->explain(
	array(
		'kind'      => 'modified',
		'node_type' => 'field',
		'before'    => array(
			'layouts' => array(
				array( 'key' => 'layout_removed', 'name' => 'removed', 'label' => 'Removed', 'display' => 'block' ),
				array(
					'key'        => 'layout_modified',
					'name'       => 'feature',
					'label'      => 'Feature',
					'display'    => 'block',
					'settings'   => array( 'max' => 4, 'min' => 1 ),
					'sub_fields' => array( array( 'key' => 'field_copy', 'type' => 'text' ) ),
				),
				array(
					'key'        => 'layout_child_only',
					'name'       => 'child_only',
					'label'      => 'Child only',
					'display'    => 'block',
					'settings'   => array(),
					'sub_fields' => array( array( 'key' => 'field_child', 'type' => 'text' ) ),
				),
				'not-a-layout',
				array( 'label' => 'Missing key' ),
			),
		),
		'after'     => array(
			'layouts' => array(
				array( 'key' => 'layout_added', 'name' => 'added', 'label' => 'Added', 'display' => 'row' ),
				array(
					'key'        => 'layout_modified',
					'name'       => 'feature_new',
					'label'      => 'Feature updated',
					'display'    => 'row',
					'settings'   => array( 'button_label' => 'Add feature', 'max' => 6 ),
					'sub_fields' => array( array( 'key' => 'field_copy', 'type' => 'textarea' ) ),
				),
				array(
					'key'        => 'layout_child_only',
					'name'       => 'child_only',
					'label'      => 'Child only',
					'display'    => 'block',
					'settings'   => array(),
					'sub_fields' => array( array( 'key' => 'field_child', 'type' => 'number' ) ),
				),
				array( 'key' => '', 'label' => 'Empty key' ),
			),
		),
	)
);
acf_schema_guard_explainer_assert(
	array(
		'Layout added: "Added" ("added"), key "layout_added".',
		'Layout "layout_modified" name: "feature" -> "feature_new"',
		'Layout "layout_modified" label: "Feature" -> "Feature updated"',
		'Layout "layout_modified" display: "block" -> "row"',
		'Layout "layout_modified" setting "button_label" added: "Add feature"',
		'Layout "layout_modified" setting "max": 4 -> 6',
		'Layout "layout_modified" setting "min" removed: 1',
		'Layout removed: "Removed" ("removed"), key "layout_removed".',
	) === $layouts['details'],
	'Flexible Content layout details are wrong, unordered, or duplicate child changes.'
);

$malformed_layouts = $explainer->explain(
	array(
		'kind'      => 'modified',
		'node_type' => 'field',
		'before'    => array( 'layouts' => 'invalid' ),
		'after'     => array( 'layouts' => array( null, array(), array( 'key' => 42 ) ) ),
	)
);
acf_schema_guard_explainer_assert( array() === $malformed_layouts['details'], 'Malformed layout collections should be ignored safely.' );

echo "Schema change explainer assertions passed.\n";
