<?php
/**
 * Enables a deliberately local-only Pro preview for product development.
 *
 * @package ACFSchemaGuard
 */

namespace AcfSchemaGuard\Licensing;

if ( ! defined( 'ABSPATH' ) ) {

	exit;
}

final class LocalPreviewStateProvider {
	const TOKEN = 'acf-schema-guard-local-preview';

	/** @var callable */
	private $environment_type_callback;

	/**
	 * @param callable|null $environment_type_callback Gets the current environment type.
	 */
	public function __construct( $environment_type_callback = null ) {
		$this->environment_type_callback = is_callable( $environment_type_callback ) ? $environment_type_callback : array( $this, 'environment_type' );
	}

	/**
	 * Gets the local preview state, never a production license assertion.
	 *
	 * @return LicenseState
	 */
	public function state() {
		$token = defined( 'ACF_SCHEMA_GUARD_LOCAL_PRO_PREVIEW_TOKEN' ) ? (string) ACF_SCHEMA_GUARD_LOCAL_PRO_PREVIEW_TOKEN : '';
		$setting = function_exists( 'get_option' ) ? (bool) get_option( 'acf_schema_guard_local_pro_preview', false ) : false;

		if ( 'local' === call_user_func( $this->environment_type_callback ) && ( hash_equals( self::TOKEN, $token ) || $setting ) ) {
			return LicenseState::valid( ProCapabilities::all() );
		}

		return LicenseState::unverifiable();
	}

	/** @return string */
	private function environment_type() {
		return function_exists( 'wp_get_environment_type' ) ? wp_get_environment_type() : 'production';
	}
}
