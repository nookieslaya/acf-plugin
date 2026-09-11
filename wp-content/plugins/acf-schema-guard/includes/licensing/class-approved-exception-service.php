<?php
/**
 * Persists and resolves bounded, capability-gated exception records.
 *
 * @package ACFSchemaGuard
 */

namespace AcfSchemaGuard\Licensing;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class ApprovedExceptionService {
	const OPTION_NAME = 'acf_schema_guard_approved_exceptions';
	const MAX_RECORDS = 100;

	private $capabilities;

	public function __construct( CapabilityService $capabilities ) {
		$this->capabilities = $capabilities;
	}

	public function active_for( array $finding ) {
		$fingerprint = FindingFingerprint::from_finding( $finding );
		foreach ( $this->all() as $exception ) {
			if ( $exception->fingerprint() === $fingerprint && $exception->is_active() ) {
				return $exception;
			}
		}

		return null;
	}

	public function all() {
		$records    = get_option( self::OPTION_NAME, array() );
		$exceptions = array();
		foreach ( is_array( $records ) ? $records : array() as $record ) {
			$exception = new ApprovedException( is_array( $record ) ? $record : array() );
			if ( $exception->is_valid() ) {
				$exceptions[] = $exception;
			}
		}

		return $exceptions;
	}

	public function save( array $finding, $reason, $author_id, $author_name, $expires_at = '' ) {
		if ( ! $this->capabilities->can( ProCapabilities::APPROVED_EXCEPTIONS )->is_allowed() ) {
			return false;
		}

		$record = new ApprovedException(
			array(
				'fingerprint' => FindingFingerprint::from_finding( $finding ),
				'reason'      => $reason,
				'author_id'   => $author_id,
				'author_name' => $author_name,
				'created_at'  => gmdate( 'c' ),
				'expires_at'  => $expires_at,
			)
		);
		if ( ! $record->is_valid() || ( '' !== $expires_at && ! $record->is_active() ) ) {
			return false;
		}

		$records = array();
		foreach ( $this->all() as $exception ) {
			if ( $exception->fingerprint() !== $record->fingerprint() ) {
				$records[] = $exception->to_array();
			}
		}
		$records[] = $record->to_array();

		return update_option( self::OPTION_NAME, array_slice( $records, -self::MAX_RECORDS ), false );
	}

	public function revoke( $fingerprint ) {
		if ( ! $this->capabilities->can( ProCapabilities::APPROVED_EXCEPTIONS )->is_allowed() ) {
			return false;
		}

		$records = array();
		foreach ( $this->all() as $exception ) {
			if ( $exception->fingerprint() !== (string) $fingerprint ) {
				$records[] = $exception->to_array();
			}
		}

		return update_option( self::OPTION_NAME, $records, false );
	}
}
