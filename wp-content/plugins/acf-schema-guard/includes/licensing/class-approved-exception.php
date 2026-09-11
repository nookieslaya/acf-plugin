<?php
/**
 * Represents one explicit, time-bounded approval for a schema finding.
 *
 * @package ACFSchemaGuard
 */

namespace AcfSchemaGuard\Licensing;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class ApprovedException {
	private $fingerprint;
	private $reason;
	private $author_id;
	private $author_name;
	private $created_at;
	private $expires_at;

	public function __construct( array $record ) {
		$this->fingerprint = isset( $record['fingerprint'] ) ? (string) $record['fingerprint'] : '';
		$this->reason      = isset( $record['reason'] ) ? trim( (string) $record['reason'] ) : '';
		$this->author_id   = isset( $record['author_id'] ) ? absint( $record['author_id'] ) : 0;
		$this->author_name = isset( $record['author_name'] ) ? sanitize_text_field( $record['author_name'] ) : '';
		$this->created_at  = isset( $record['created_at'] ) ? (string) $record['created_at'] : '';
		$this->expires_at  = isset( $record['expires_at'] ) ? (string) $record['expires_at'] : '';
	}

	public function is_valid() {
		return 64 === strlen( $this->fingerprint ) && ctype_xdigit( $this->fingerprint ) && '' !== $this->reason && 0 < $this->author_id && '' !== $this->author_name && false !== strtotime( $this->created_at );
	}

	public function is_active( $now = null ) {
		if ( ! $this->is_valid() ) {
			return false;
		}

		if ( '' === $this->expires_at ) {
			return true;
		}

		$expires_at = strtotime( $this->expires_at );
		$now        = null === $now ? time() : (int) $now;

		return false !== $expires_at && $expires_at > $now;
	}

	public function fingerprint() {
		return $this->fingerprint;
	}

	public function to_array() {
		return array(
			'fingerprint' => $this->fingerprint,
			'reason'      => $this->reason,
			'author_id'   => $this->author_id,
			'author_name' => $this->author_name,
			'created_at'  => $this->created_at,
			'expires_at'  => $this->expires_at,
		);
	}
}
