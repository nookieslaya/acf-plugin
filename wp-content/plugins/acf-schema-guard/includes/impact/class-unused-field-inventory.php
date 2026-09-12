<?php
/**
 * Produces review-only coverage signals for normalized ACF fields.
 *
 * @package ACFSchemaGuard
 */

namespace AcfSchemaGuard\Impact;

if ( ! defined( 'ABSPATH' ) ) { exit; }

final class UnusedFieldInventory {
	public function analyze( array $schema, array $references, array $dynamic_references = array() ) { $items = array(); $this->collect( isset( $schema['field_groups'] ) ? $schema['field_groups'] : array(), $this->referenced_names( $references ), ! empty( $dynamic_references ), $items ); return $items; }
	private function referenced_names( array $references ) { $names = array(); foreach ( $references as $reference ) { $data = is_object( $reference ) && method_exists( $reference, 'to_array' ) ? $reference->to_array() : $reference; if ( is_array( $data ) && isset( $data['field_name'] ) ) { $names[] = $data['field_name']; } } return $names; }
	private function collect( array $nodes, array $names, $dynamic, array &$items ) { foreach ( $nodes as $node ) { if ( ! is_array( $node ) ) { continue; } if ( isset( $node['name'] ) && '' !== $node['name'] ) { $count = count( array_keys( $names, $node['name'], true ) ); $items[] = array( 'field_key' => isset( $node['key'] ) ? (string) $node['key'] : '', 'field_name' => $node['name'], 'literal_reference_count' => $count, 'dynamic_reference_present' => $dynamic, 'review_state' => 0 === $count ? ( $dynamic ? 'manual_review_required' : 'potentially_unused' ) : 'referenced', 'scope_notice' => 'Only configured scanner roots and supported literal PHP ACF calls are included.' ); } foreach ( array( 'fields', 'sub_fields', 'layouts' ) as $key ) { if ( isset( $node[ $key ] ) && is_array( $node[ $key ] ) ) { $this->collect( $node[ $key ], $names, $dynamic, $items ); } } } }
}
