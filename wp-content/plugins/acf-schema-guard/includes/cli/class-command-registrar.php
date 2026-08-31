<?php
/**
 * Isolates optional WP-CLI command registration from the web runtime.
 *
 * @package ACFSchemaGuard
 */

namespace AcfSchemaGuard\Cli;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class CommandRegistrar {
	/**
	 * Registers one command with the active WP-CLI runtime.
	 *
	 * @param string   $name Command name without the `wp` prefix.
	 * @param callable $callable Command callback.
	 * @return void
	 */
	public function register( $name, $callable ) {
		\WP_CLI::add_command( $name, $callable );
	}
}
