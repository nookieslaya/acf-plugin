<?php
define( 'ABSPATH', __DIR__ . '/' );
function absint( $value ) { return abs( (int) $value ); }
function sanitize_text_field( $value ) { return trim( (string) $value ); }
function wp_json_encode( $value ) { return json_encode( $value ); }
require_once dirname( __DIR__ ) . '/includes/licensing/class-approved-exception.php';
require_once dirname( __DIR__ ) . '/includes/licensing/class-finding-fingerprint.php';
$finding = array( 'change' => array( 'kind' => 'modified', 'node_type' => 'field', 'path' => array( 'group_a', 'field_a' ), 'before' => array( 'name' => 'old' ), 'after' => array( 'name' => 'new' ) ) );
$fingerprint = \AcfSchemaGuard\Licensing\FindingFingerprint::from_finding( $finding );
$active = new \AcfSchemaGuard\Licensing\ApprovedException( array( 'fingerprint' => $fingerprint, 'reason' => 'Reviewed migration.', 'author_id' => 1, 'author_name' => 'Ada', 'created_at' => '2026-09-11T12:00:00Z', 'expires_at' => '2026-09-12T12:00:00Z' ) );
if ( ! $active->is_active( strtotime( '2026-09-11T13:00:00Z' ) ) || $active->is_active( strtotime( '2026-09-13T13:00:00Z' ) ) || $fingerprint === \AcfSchemaGuard\Licensing\FindingFingerprint::from_finding( array( 'change' => array( 'kind' => 'modified', 'node_type' => 'field', 'path' => array( 'group_a', 'field_b' ), 'before' => array( 'name' => 'old' ), 'after' => array( 'name' => 'new' ) ) ) ) ) { throw new RuntimeException( 'Approved exception assertions failed.' ); }
echo "Approved exception assertions passed.\n";
