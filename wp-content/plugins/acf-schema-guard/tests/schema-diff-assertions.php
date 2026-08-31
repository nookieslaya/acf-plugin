<?php
define( 'ABSPATH', __DIR__ . '/' );
require_once dirname( __DIR__ ) . '/includes/diff/class-schema-change.php';
require_once dirname( __DIR__ ) . '/includes/diff/class-schema-diff.php';
require_once dirname( __DIR__ ) . '/includes/diff/class-schema-differ.php';
$differ = new \AcfSchemaGuard\Diff\SchemaDiffer();
$before = array( 'field_groups' => array( array( 'key' => 'group_a', 'fields' => array( array( 'key' => 'field_a', 'type' => 'text' ) ) ) ) );
$after = array( 'field_groups' => array( array( 'key' => 'group_a', 'fields' => array( array( 'key' => 'field_a', 'type' => 'number' ), array( 'key' => 'field_b', 'type' => 'text' ) ) ) ) );
if ( 3 !== count( $differ->compare( $before, $after )->to_array()['changes'] ) || array() !== $differ->compare( $before, $before )->to_array()['changes'] ) { exit( 1 ); }
$layout_before = array( 'field_groups' => array( array( 'key' => 'g', 'fields' => array( array( 'key' => 'flex', 'layouts' => array( array( 'key' => 'layout', 'sub_fields' => array( array( 'key' => 'inner', 'type' => 'text' ) ) ) ) ) ) ) ) );
$layout_after = array( 'field_groups' => array( array( 'key' => 'g', 'fields' => array( array( 'key' => 'flex', 'layouts' => array( array( 'key' => 'layout', 'sub_fields' => array( array( 'key' => 'inner', 'type' => 'number' ) ) ) ) ) ) ) ) );
if ( 0 === count( $differ->compare( $layout_before, $layout_after )->to_array()['changes'] ) ) { exit( 1 ); }
echo "Schema diff assertions passed.\n";
