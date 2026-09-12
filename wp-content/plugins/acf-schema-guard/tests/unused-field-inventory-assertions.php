<?php
define( 'ABSPATH', __DIR__ . '/' ); require_once dirname( __DIR__ ) . '/includes/impact/class-unused-field-inventory.php';
$items = ( new \AcfSchemaGuard\Impact\UnusedFieldInventory() )->analyze( array( 'field_groups' => array( array( 'fields' => array( array( 'name' => 'used' ), array( 'name' => 'review' ) ) ) ) ), array( array( 'field_name' => 'used' ) ), array( array( 'expression' => 'get_field( $name )' ) ) );
if ( 'referenced' !== $items[0]['review_state'] || 'manual_review_required' !== $items[1]['review_state'] ) { throw new RuntimeException( 'Unused field inventory assertion failed.' ); } echo "Unused field inventory assertions passed.\n";
