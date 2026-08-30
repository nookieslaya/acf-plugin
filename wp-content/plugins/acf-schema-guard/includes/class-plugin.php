<?php
/**
 * Plugin composition root.
 *
 * @package ACFSchemaGuard
 */

namespace AcfSchemaGuard;

use AcfSchemaGuard\Acf\AcfEnvironment;
use AcfSchemaGuard\Acf\AcfEnvironmentProvider;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once ACF_SCHEMA_GUARD_PATH . 'includes/acf/class-field-group-descriptor.php';
require_once ACF_SCHEMA_GUARD_PATH . 'includes/acf/class-acf-environment.php';
require_once ACF_SCHEMA_GUARD_PATH . 'includes/acf/class-acf-environment-provider.php';
require_once ACF_SCHEMA_GUARD_PATH . 'includes/acf/interface-full-schema-source.php';
require_once ACF_SCHEMA_GUARD_PATH . 'includes/acf/class-acf-schema-source.php';
require_once ACF_SCHEMA_GUARD_PATH . 'includes/schema/class-canonical-value.php';
require_once ACF_SCHEMA_GUARD_PATH . 'includes/schema/class-normalized-field.php';
require_once ACF_SCHEMA_GUARD_PATH . 'includes/schema/class-normalized-field-group.php';
require_once ACF_SCHEMA_GUARD_PATH . 'includes/schema/class-normalized-schema.php';
require_once ACF_SCHEMA_GUARD_PATH . 'includes/schema/class-schema-normalizer.php';

/**
 * Coordinates the plugin lifecycle without depending on ACF at load time.
 */
final class Plugin {
	/**
	 * Singleton instance.
	 *
	 * @var Plugin|null
	 */
	private static $instance = null;

	/**
	 * Whether bootstrap completed.
	 *
	 * @var bool
	 */
	private $is_booted = false;

	/**
	 * Read-only ACF environment provider.
	 *
	 * @var AcfEnvironmentProvider|null
	 */
	private $acf_environment_provider = null;

	/**
	 * Gets the plugin instance.
	 *
	 * @return Plugin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Boots the plugin once WordPress has loaded all plugins.
	 *
	 * @return void
	 */
	public function boot() {
		if ( $this->is_booted ) {
			return;
		}

		$this->acf_environment_provider = new AcfEnvironmentProvider();
		$this->is_booted = true;

		/**
		 * Fires once the ACF Schema Guard plugin service is ready.
		 *
		 * @param Plugin $plugin Initialized plugin service.
		 */
		do_action( 'acf_schema_guard/booted', $this );
	}

	/**
	 * Gets a fresh, read-only description of the current ACF environment.
	 *
	 * @return AcfEnvironment
	 */
	public function acf_environment() {
		if ( null === $this->acf_environment_provider ) {
			$this->acf_environment_provider = new AcfEnvironmentProvider();
		}

		return $this->acf_environment_provider->discover();
	}

	/**
	 * Prevents external instantiation.
	 */
	private function __construct() {}
}
