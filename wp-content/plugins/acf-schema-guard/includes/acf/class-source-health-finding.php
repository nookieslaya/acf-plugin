<?php
/**
 * Immutable comparison result for one ACF field group.
 *
 * @package ACFSchemaGuard
 */

namespace AcfSchemaGuard\Acf;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class SourceHealthFinding {
	const STATUS_ALIGNED       = 'aligned';
	const STATUS_DATABASE_ONLY = 'database_only';
	const STATUS_JSON_ONLY     = 'json_only';
	const STATUS_DIVERGENT     = 'divergent';

	/** @var string */
	private $field_group_key;

	/** @var string */
	private $title;

	/** @var string */
	private $status;

	/** @var array|null */
	private $database_group;

	/** @var array|null */
	private $json_group;
	private $direction;

	/**
	 * @param string     $field_group_key Stable ACF field-group key.
	 * @param string     $title           Readable field-group title.
	 * @param string     $status          Source-health classification.
	 * @param array|null $database_group  Canonical database representation.
	 * @param array|null $json_group      Canonical Local JSON representation.
	 */
	public function __construct( $field_group_key, $title, $status, $database_group, $json_group, $direction = 'unknown' ) {
		$this->field_group_key = (string) $field_group_key;
		$this->title           = (string) $title;
		$this->status          = self::valid_status( $status ) ? $status : self::STATUS_DIVERGENT;
		$this->database_group  = is_array( $database_group ) ? $database_group : null;
		$this->json_group      = is_array( $json_group ) ? $json_group : null;
		$this->direction       = in_array( $direction, array( 'json_newer', 'database_newer', 'equal', 'unknown' ), true ) ? $direction : 'unknown';
	}

	/** @return string */
	public function field_group_key() {
		return $this->field_group_key;
	}

	/** @return string */
	public function title() {
		return $this->title;
	}

	/** @return string */
	public function status() {
		return $this->status;
	}

	/** @return array|null */
	public function database_group() {
		return $this->database_group;
	}

	/** @return array|null */
	public function json_group() {
		return $this->json_group;
	}

	/** @return string */
	public function direction() { return $this->direction; }

	/** @return array<string, mixed> */
	public function to_array() {
		return array(
			'field_group_key' => $this->field_group_key,
			'title'           => $this->title,
			'status'          => $this->status,
			'database_group'  => $this->database_group,
			'json_group'      => $this->json_group,
			'direction'       => $this->direction,
		);
	}

	/** @return bool */
	private static function valid_status( $status ) {
		return in_array( $status, array( self::STATUS_ALIGNED, self::STATUS_DATABASE_ONLY, self::STATUS_JSON_ONLY, self::STATUS_DIVERGENT ), true );
	}
}
