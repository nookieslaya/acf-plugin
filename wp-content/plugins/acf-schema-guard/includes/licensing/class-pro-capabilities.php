<?php
/**
 * Defines the named capabilities reserved for future Pro workflows.
 *
 * @package ACFSchemaGuard
 */

namespace AcfSchemaGuard\Licensing;

if ( ! defined( 'ABSPATH' ) ) {

	exit;
}

final class ProCapabilities {
	const CONFIGURABLE_RISK_POLICIES = 'configurable_risk_policies';
	const REVIEW_READY_REPORTS       = 'review_ready_reports';

	/**
	 * Gets every capability known to this release.
	 *
	 * @return string[]
	 */
	public static function all() {
		return array(
			self::CONFIGURABLE_RISK_POLICIES,
			self::REVIEW_READY_REPORTS,
		);
	}

	/**
	 * Checks whether a capability is known.
	 *
	 * @param string $capability Capability identifier.
	 * @return bool
	 */
	public static function is_known( $capability ) {
		return in_array( (string) $capability, self::all(), true );
	}
}
