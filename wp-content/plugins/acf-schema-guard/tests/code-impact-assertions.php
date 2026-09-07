<?php
define( 'ABSPATH', __DIR__ . '/' );
require_once dirname( __DIR__ ) . '/includes/impact/class-code-impact.php';
require_once dirname( __DIR__ ) . '/includes/impact/class-code-impact-analyzer.php';
$analyzer = new \AcfSchemaGuard\Impact\CodeImpactAnalyzer();
$references = array( array( 'field_name' => 'hero_title', 'path' => 'template.php', 'line' => 12, 'expression' => "get_field('hero_title')" ) );
$removed = array( array( 'kind' => 'removed', 'node_type' => 'field', 'before' => array( 'name' => 'hero_title' ), 'after' => null ) );
$impacts = $analyzer->analyze( $removed, $references );
if ( 1 !== count( $impacts ) || 'critical' !== $impacts[0]->to_array()['severity'] ) { fwrite( STDERR, "Code impact assertion failed.\n" ); exit( 1 ); }
$renamed = array( array( 'kind' => 'modified', 'node_type' => 'field', 'before' => array( 'name' => 'hero_title' ), 'after' => array( 'name' => 'hero_heading' ) ) );
$typed = array( array( 'kind' => 'modified', 'node_type' => 'field', 'before' => array( 'name' => 'hero_title', 'type' => 'text' ), 'after' => array( 'name' => 'hero_title', 'type' => 'textarea' ) ) );
if ( 'high' !== $analyzer->analyze( $renamed, $references )[0]->to_array()['severity'] || 'warning' !== $analyzer->analyze( $typed, $references )[0]->to_array()['severity'] || array() !== $analyzer->analyze( $removed, array() ) ) { fwrite( STDERR, "Code impact rule assertion failed.\n" ); exit( 1 ); }
echo "Code impact assertions passed.\n";
