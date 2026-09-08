<?php
define( 'ABSPATH', __DIR__ . '/' );
require_once dirname( __DIR__ ) . '/includes/impact/class-code-impact.php';
require_once dirname( __DIR__ ) . '/includes/impact/class-code-impact-analyzer.php';
$analyzer = new \AcfSchemaGuard\Impact\CodeImpactAnalyzer();
$references = array(
	array(
		'field_name' => 'hero_title',
		'path'       => 'template.php',
		'line'       => 12,
		'expression' => "get_field('hero_title')",
	),
);
$removed = array( array( 'kind' => 'removed', 'node_type' => 'field', 'before' => array( 'name' => 'hero_title' ), 'after' => null ) );
$renamed = array( array( 'kind' => 'modified', 'node_type' => 'field', 'before' => array( 'name' => 'hero_title' ), 'after' => array( 'name' => 'hero_heading' ) ) );
$typed   = array( array( 'kind' => 'modified', 'node_type' => 'field', 'before' => array( 'name' => 'hero_title', 'type' => 'text' ), 'after' => array( 'name' => 'hero_title', 'type' => 'textarea' ) ) );

$impacts = $analyzer->analyze( $removed, array_merge( $references, $references ) );
if ( 1 !== count( $impacts ) || 'critical' !== $impacts[0]->to_array()['severity'] ) {
	fwrite( STDERR, "Removed-field impact assertion failed.\n" );
	exit( 1 );
}

if ( 'high' !== $analyzer->analyze( $renamed, $references )[0]->to_array()['severity'] ) {
	fwrite( STDERR, "Renamed-field impact assertion failed.\n" );
	exit( 1 );
}

if ( 'warning' !== $analyzer->analyze( $typed, $references )[0]->to_array()['severity'] ) {
	fwrite( STDERR, "Type-change impact assertion failed.\n" );
	exit( 1 );
}

if ( array() !== $analyzer->analyze( $removed, array() ) ) {
	fwrite( STDERR, "Empty reference assertion failed.\n" );
	exit( 1 );
}
echo "Code impact assertions passed.\n";
