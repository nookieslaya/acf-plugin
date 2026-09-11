<?php
/**
 * Immutable provider-neutral license state.
 *
 * @package ACFSchemaGuard
 */

namespace AcfSchemaGuard\Licensing;

if ( ! defined( 'ABSPATH' ) ) {

	exit;
}

final class LicenseState {
	const VALID        = 'valid';
	const EXPIRED      = 'expired';
	const INVALID      = 'invalid';
	const MISSING      = 'missing';
	const UNVERIFIABLE = 'unverifiable';

	/** @var string */
	private $status;

	/** @var string[] */
	private $capabilities;

	/**
	 * @param string   $status License status.
	 * @param string[] $capabilities Allowed Pro capability identifiers.
	 */
	public function __construct( $status, array $capabilities = array() ) {
		$this->status       = self::is_known_status( $status ) ? (string) $status : self::UNVERIFIABLE;
		$this->capabilities = array_values( array_unique( array_filter( $capabilities, array( ProCapabilities::class, 'is_known' ) ) ) );
	}

	/** @return LicenseState */
	public static function unverifiable() {
		return new self( self::UNVERIFIABLE );
	}

	/**
	 * @param string[] $capabilities Allowed capabilities.
	 * @return LicenseState
	 */
	public static function valid( array $capabilities ) {
		return new self( self::VALID, $capabilities );
	}

	/** @return string */
	public function status() {
		return $this->status;
	}

	/** @return string[] */
	public function capabilities() {
		return $this->capabilities;
	}

	/**
	 * @param string $capability Capability identifier.
	 * @return bool
	 */
	public function allows( $capability ) {
		return self::VALID === $this->status && in_array( (string) $capability, $this->capabilities, true );
	}

	/**
	 * @param string $status Candidate status.
	 * @return bool
	 */
	private static function is_known_status( $status ) {
		return in_array( (string) $status, array( self::VALID, self::EXPIRED, self::INVALID, self::MISSING, self::UNVERIFIABLE ), true );
	}
}
