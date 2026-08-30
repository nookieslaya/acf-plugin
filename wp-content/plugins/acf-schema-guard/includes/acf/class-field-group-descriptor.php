<?php
/**
 * Read-only descriptor for an ACF field group.
 *
 * @package ACFSchemaGuard
 */

namespace AcfSchemaGuard\Acf;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Represents only the field-group metadata needed before normalization.
 */
final class FieldGroupDescriptor {
	const SOURCE_RUNTIME    = 'runtime';
	const SOURCE_LOCAL_JSON = 'local_json';

	/** @var string */
	private $key;

	/** @var string */
	private $title;

	/** @var bool */
	private $is_active;

	/** @var string */
	private $source;

	/** @var string|null */
	private $local_json_file;

	/**
	 * @param string      $key             ACF field-group key.
	 * @param string      $title           Field-group title.
	 * @param bool        $is_active       ACF active state.
	 * @param string      $source          Discovery source.
	 * @param string|null $local_json_file Mapped Local JSON file.
	 */
	public function __construct( $key, $title, $is_active, $source, $local_json_file = null ) {
		$this->key             = (string) $key;
		$this->title           = (string) $title;
		$this->is_active       = (bool) $is_active;
		$this->source          = self::SOURCE_LOCAL_JSON === $source ? self::SOURCE_LOCAL_JSON : self::SOURCE_RUNTIME;
		$this->local_json_file = null === $local_json_file ? null : (string) $local_json_file;
	}

	/** @return string */
	public function key() {
		return $this->key;
	}

	/** @return string */
	public function title() {
		return $this->title;
	}

	/** @return bool */
	public function is_active() {
		return $this->is_active;
	}

	/** @return string */
	public function source() {
		return $this->source;
	}

	/** @return string|null */
	public function local_json_file() {
		return $this->local_json_file;
	}
}
