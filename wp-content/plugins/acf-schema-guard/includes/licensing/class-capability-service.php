<?php
/**
 * Resolves named Pro capabilities from provider-neutral state.
 *
 * @package ACFSchemaGuard
 */

namespace AcfSchemaGuard\Licensing;

if ( ! defined( 'ABSPATH' ) ) {

	exit;
}

final class CapabilityService {
	/** @var LicenseState */
	private $state;

	public function __construct( LicenseState $state ) {
		$this->state = $state;
	}

	/**
	 * @param string $capability Capability identifier.
	 * @return CapabilityDecision
	 */
	public function can( $capability ) {
		$capability = (string) $capability;

		if ( ! ProCapabilities::is_known( $capability ) ) {
			return new CapabilityDecision( false, $capability, 'This Pro capability is not recognized by this version of ACF Schema Guard.' );
		}

		if ( $this->state->allows( $capability ) ) {
			return new CapabilityDecision( true, $capability, '' );
		}

		return new CapabilityDecision( false, $capability, $this->denial_reason() );
	}

	/** @return string */
	private function denial_reason() {
		$reasons = array(
			LicenseState::EXPIRED      => 'Your Pro license has expired. Free schema safety features remain available.',
			LicenseState::INVALID      => 'This Pro license could not be validated. Free schema safety features remain available.',
			LicenseState::MISSING      => 'This is a Pro feature. Free schema safety features remain available.',
			LicenseState::UNVERIFIABLE => 'This is a Pro feature and no verified license is available. Free schema safety features remain available.',
		);

		return isset( $reasons[ $this->state->status() ] ) ? $reasons[ $this->state->status() ] : $reasons[ LicenseState::UNVERIFIABLE ];
	}
}
