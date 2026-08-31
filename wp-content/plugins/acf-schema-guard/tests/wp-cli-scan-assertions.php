<?php
namespace WP_CLI\Utils {
	function format_items( $format, $items, $fields ) {
		\WP_CLI::$formatted = array(
			'format' => $format,
			'items'  => $items,
			'fields' => $fields,
		);
	}
}

namespace {
	class WP_CLI {
		public static $commands = array();
		public static $formatted = null;
		public static $lines = array();
		public static $successes = array();

		public static function add_command( $name, $callable ) {
			self::$commands[ $name ] = $callable;
		}

		public static function error( $message ) {
			throw new \RuntimeException( $message );
		}

		public static function line( $message ) {
			self::$lines[] = $message;
		}

		public static function success( $message ) {
			self::$successes[] = $message;
		}
	}

	define( 'ABSPATH', __DIR__ . '/' );

	require_once dirname( __DIR__ ) . '/includes/scanner/class-code-usage-reference.php';
	require_once dirname( __DIR__ ) . '/includes/scanner/interface-code-usage-scanner.php';
	require_once dirname( __DIR__ ) . '/includes/scanner/class-code-usage-scanner-service.php';
	require_once dirname( __DIR__ ) . '/includes/scanner/class-php-acf-usage-scanner.php';
	require_once dirname( __DIR__ ) . '/includes/cli/class-command-registrar.php';
	require_once dirname( __DIR__ ) . '/includes/cli/class-scan-command.php';

	function acf_schema_guard_cli_assert( $condition, $message ) {
		if ( ! $condition ) {
			throw new \RuntimeException( $message );
		}
	}

	$root = sys_get_temp_dir() . '/acf-schema-guard-cli-' . uniqid();
	mkdir( $root );
	file_put_contents( $root . '/fixture.php', "<?php\nget_field( 'hero_title' );\n" );

	try {
		$command = new \AcfSchemaGuard\Cli\ScanCommand(
			new \AcfSchemaGuard\Scanner\CodeUsageScannerService(
				array( new \AcfSchemaGuard\Scanner\PhpAcfUsageScanner() )
			)
		);

		( new \AcfSchemaGuard\Cli\CommandRegistrar() )->register(
			'acf-schema-guard scan',
			array( $command, 'scan' )
		);
		acf_schema_guard_cli_assert( isset( WP_CLI::$commands['acf-schema-guard scan'] ), 'Command was not registered.' );

		$command->scan( array( $root ), array() );
		acf_schema_guard_cli_assert( 'table' === WP_CLI::$formatted['format'], 'Table format was not used.' );
		acf_schema_guard_cli_assert( 'hero_title' === WP_CLI::$formatted['items'][0]['field_name'], 'Table output missed the field.' );

		$command->scan( array( $root ), array( 'format' => 'json' ) );
		$json = json_decode( WP_CLI::$lines[0], true );
		acf_schema_guard_cli_assert( 'hero_title' === $json[0]['field_name'], 'JSON output missed the field.' );

		$empty_root = $root . '/empty';
		mkdir( $empty_root );
		$command->scan( array( $empty_root ), array() );
		acf_schema_guard_cli_assert( ! empty( WP_CLI::$successes ), 'Empty scan did not report success.' );

		try {
			$command->scan( array( $root . '/missing' ), array() );
			throw new \RuntimeException( 'Invalid source root did not fail.' );
		} catch ( \RuntimeException $exception ) {
			acf_schema_guard_cli_assert(
				false !== strpos( $exception->getMessage(), 'not a readable directory' ),
				'Invalid source root returned the wrong error.'
			);
		}
	} finally {
		unlink( $root . '/fixture.php' );
		rmdir( $root . '/empty' );
		rmdir( $root );
	}

	echo "WP-CLI scan assertions passed.\n";
}
