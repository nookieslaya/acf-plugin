<?php

define( 'ABSPATH', __DIR__ . '/' );

$acf_schema_guard_options = array();
function get_option( $key, $default = false ) { global $acf_schema_guard_options; return isset( $acf_schema_guard_options[ $key ] ) ? $acf_schema_guard_options[ $key ] : $default; }
function update_option( $key, $value ) { global $acf_schema_guard_options; $acf_schema_guard_options[ $key ] = $value; return true; }

$root = sys_get_temp_dir() . '/acf-schema-guard-settings-' . uniqid();
mkdir( $root . '/wp-content/themes/example', 0777, true );
mkdir( $root . '/wp-content/plugins/example', 0777, true );
mkdir( $root . '/outside', 0777, true );
define( 'WP_CONTENT_DIR', $root . '/wp-content' );

require_once dirname( __DIR__ ) . '/includes/scanner/class-scanner-configuration.php';

$configuration = new \AcfSchemaGuard\Scanner\ScannerConfiguration();
$configuration->save( array( $root . '/wp-content/themes/example', $root . '/outside' ) );
$roots = $configuration->roots();

if ( array( realpath( $root . '/wp-content/themes/example' ) ) !== $roots ) {
	fwrite( STDERR, "Scanner configuration assertion failed.\n" );
	exit( 1 );
}

rmdir( $root . '/wp-content/themes/example' );
rmdir( $root . '/wp-content/themes' );
rmdir( $root . '/wp-content/plugins/example' );
rmdir( $root . '/wp-content/plugins' );
rmdir( $root . '/wp-content' );
rmdir( $root . '/outside' );
rmdir( $root );

echo "Scanner configuration assertions passed.\n";
