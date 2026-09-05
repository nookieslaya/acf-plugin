<?php
/**
 * Isolated assertions for portable baseline WP-CLI commands.
 *
 * @package ACFSchemaGuard
 */

namespace WP_CLI\Utils {
	function format_items( $format, $items, $fields ) {
		\WP_CLI::$items = $items;
	}
}

namespace {
	class WP_CLI {
		public static $items = array();
		public static $lines = array();
		public static $successes = array();
		public static function error( $message ) { throw new \RuntimeException( $message ); }
		public static function line( $message ) { self::$lines[] = $message; }
		public static function success( $message ) { self::$successes[] = $message; }
	}

	define( 'ABSPATH', __DIR__ . '/' );

	require_once dirname( __DIR__ ) . '/includes/baseline/class-schema-baseline-file.php';
	foreach ( array( 'class-schema-change.php', 'class-schema-diff.php', 'class-schema-differ.php', 'class-risk-finding.php', 'class-risk-classifier.php', 'class-snapshot-analysis.php', 'class-snapshot-analysis-service.php' ) as $file ) {
		require_once dirname( __DIR__ ) . '/includes/diff/' . $file;
	}
	require_once dirname( __DIR__ ) . '/includes/cli/class-baseline-command.php';

	function acf_schema_guard_cli_baseline_assert( $condition, $message ) {
		if ( ! $condition ) {
			throw new \RuntimeException( $message );
		}
	}

	$schema = array( 'schema_version' => 1, 'field_groups' => array( array( 'key' => 'group', 'fields' => array( array( 'key' => 'field', 'type' => 'text' ) ) ) ) );
	$path   = tempnam( sys_get_temp_dir(), 'acf-schema-baseline-' );
	unlink( $path );
	$service = new \AcfSchemaGuard\Diff\SnapshotAnalysisService( new \AcfSchemaGuard\Diff\SchemaDiffer(), new \AcfSchemaGuard\Diff\RiskClassifier() );
	$command = new \AcfSchemaGuard\Cli\BaselineCommand( new \AcfSchemaGuard\Baseline\SchemaBaselineFile(), $service, function() use ( $schema ) { return $schema; } );

	$command->export( array( $path ), array() );
	acf_schema_guard_cli_baseline_assert( is_file( $path ), 'Baseline export did not create the file.' );
	$command->check( array( $path ), array( 'format' => 'json', 'fail-on-breaking' => true ) );
	acf_schema_guard_cli_baseline_assert( isset( json_decode( WP_CLI::$lines[0], true )['findings'] ), 'Baseline JSON output is invalid.' );

	$breaking_schema = array( 'schema_version' => 1, 'field_groups' => array( array( 'key' => 'group', 'fields' => array( array( 'key' => 'field', 'type' => 'number' ) ) ) ) );
	$breaking_command = new \AcfSchemaGuard\Cli\BaselineCommand( new \AcfSchemaGuard\Baseline\SchemaBaselineFile(), $service, function() use ( $breaking_schema ) { return $breaking_schema; } );
	try {
		$breaking_command->check( array( $path ), array( 'fail-on-breaking' => true ) );
		throw new \RuntimeException( 'Breaking baseline check did not fail.' );
	} catch ( \RuntimeException $exception ) {
		acf_schema_guard_cli_baseline_assert( false !== strpos( $exception->getMessage(), 'Breaking schema changes found.' ), 'Breaking baseline error is wrong.' );
	}

	unlink( $path );

	echo "WP-CLI baseline assertions passed.\n";
}
