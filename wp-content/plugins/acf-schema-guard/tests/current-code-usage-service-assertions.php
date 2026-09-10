<?php
/**
 * Asserts that each current-code-usage scan reads the filesystem again.
 *
 * Run with: php tests/current-code-usage-service-assertions.php
 */

define( 'ABSPATH', __DIR__ . '/' );

$acf_schema_guard_options = array();
function get_option( $key, $default = false ) { global $acf_schema_guard_options; return isset( $acf_schema_guard_options[ $key ] ) ? $acf_schema_guard_options[ $key ] : $default; }
function update_option( $key, $value ) { global $acf_schema_guard_options; $acf_schema_guard_options[ $key ] = $value; return true; }

$root = sys_get_temp_dir() . '/acf-schema-guard-current-code-' . uniqid();
mkdir( $root . '/wp-content/themes/example', 0777, true );
define( 'WP_CONTENT_DIR', $root . '/wp-content' );

foreach ( array( 'class-code-usage-reference.php', 'class-dynamic-code-usage-reference.php', 'interface-code-usage-scanner.php', 'class-php-acf-usage-scanner.php', 'class-code-usage-scanner-service.php', 'class-scanner-configuration.php', 'class-current-code-usage-service.php' ) as $file ) {
	require_once dirname( __DIR__ ) . '/includes/scanner/' . $file;
}

function acf_schema_guard_current_code_assert( $condition, $message ) {
	if ( ! $condition ) {
		throw new RuntimeException( $message );
	}
}

$file = $root . '/wp-content/themes/example/template.php';
file_put_contents( $file, "<?php\nget_field( 'before_change' );\n" );

$configuration = new \AcfSchemaGuard\Scanner\ScannerConfiguration();
$configuration->save( array( 'theme:example' ) );
$service = new \AcfSchemaGuard\Scanner\CurrentCodeUsageService(
	new \AcfSchemaGuard\Scanner\CodeUsageScannerService( array( new \AcfSchemaGuard\Scanner\PhpAcfUsageScanner() ) ),
	$configuration
);

acf_schema_guard_current_code_assert( array( 'before_change' ) === array_column( array_map( static function ( $reference ) { return $reference->to_array(); }, $service->references() ), 'field_name' ), 'First scan should read the original call.' );
file_put_contents( $file, "<?php\nget_field( 'after_change' );\n" );
acf_schema_guard_current_code_assert( array( 'after_change' ) === array_column( array_map( static function ( $reference ) { return $reference->to_array(); }, $service->references() ), 'field_name' ), 'Second scan should read the current file content.' );
file_put_contents( $file, "<?php\nget_field( \$field_name );\n" );
acf_schema_guard_current_code_assert( 'get_field( $field_name )' === $service->dynamic_references()[0]->to_array()['expression'], 'Current scans should expose dynamic calls separately.' );

unlink( $file );
rmdir( $root . '/wp-content/themes/example' );
rmdir( $root . '/wp-content/themes' );
rmdir( $root . '/wp-content' );
rmdir( $root );

echo "Current code usage service assertions passed.\n";
