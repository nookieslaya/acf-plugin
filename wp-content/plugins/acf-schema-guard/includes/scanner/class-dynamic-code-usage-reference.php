<?php
/**
 * Immutable description of a supported ACF call with a dynamic field argument.
 *
 * @package ACFSchemaGuard
 */

namespace AcfSchemaGuard\Scanner;

use RuntimeException;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class DynamicCodeUsageReference {
	const REVIEW_STATUS = 'manual_review_required';

	private $strategy;
	private $path;
	private $line;
	private $expression;
	private $root;

	public function __construct( $strategy, $path, $line, $expression, $root = '' ) {
		foreach ( array( $strategy, $path, $expression ) as $value ) {
			if ( '' === trim( (string) $value ) ) {
				throw new RuntimeException( 'Dynamic code usage reference values cannot be empty.' );
			}
		}

		if ( 1 > (int) $line ) {
			throw new RuntimeException( 'Dynamic code usage reference line must be positive.' );
		}

		$this->strategy   = (string) $strategy;
		$this->path       = (string) $path;
		$this->line       = (int) $line;
		$this->expression = (string) $expression;
		$this->root       = (string) $root;
	}

	public function to_array() {
		return array(
			'reference_type' => 'dynamic',
			'review_status'  => self::REVIEW_STATUS,
			'strategy'       => $this->strategy,
			'path'           => $this->path,
			'line'           => $this->line,
			'expression'     => $this->expression,
			'root'           => $this->root,
		);
	}
}
