<?php
/**
 * Isolated assertion for a missing ACF runtime.
 *
 * @package ACFSchemaGuard
 */

define( 'ABSPATH', __DIR__ . '/' );
define( 'ACF_SCHEMA_GUARD_PATH', dirname( __DIR__ ) . '/' );

require_once dirname( __DIR__ ) . '/includes/class-plugin.php';

$schema = \AcfSchemaGuard\Plugin::instance()->normalized_schema()->to_array();

if ( array( 'schema_version' => 1, 'field_groups' => array() ) !== $schema ) {
	fwrite( STDERR, "Unavailable ACF assertion failed.\n" );
	exit( 1 );
}

echo "Unavailable ACF assertion passed.\n";
