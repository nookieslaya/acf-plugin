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

echo "Schema change explainer assertions passed.\n";
