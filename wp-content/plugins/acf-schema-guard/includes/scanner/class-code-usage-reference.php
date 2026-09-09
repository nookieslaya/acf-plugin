<?php
/**
 * Immutable description of one literal ACF call found in source code.
 *
 * @package ACFSchemaGuard
 */

namespace AcfSchemaGuard\Scanner;

use RuntimeException;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class CodeUsageReference {
	private $field_name;
	private $strategy;
	private $path;
	private $line;
	private $expression;
	private $root;

	public function __construct( $field_name, $strategy, $path, $line, $expression, $root = '' ) {
		$this->assert_non_empty_values( array( $field_name, $strategy, $path, $expression ) );

		if ( 1 > (int) $line ) {
			throw new RuntimeException( 'Code usage reference line must be positive.' );
		}

		$this->field_name = (string) $field_name;
		$this->strategy   = (string) $strategy;
		$this->path       = (string) $path;
		$this->line       = (int) $line;
		$this->expression = (string) $expression;
		$this->root       = (string) $root;
	}

	/** @return array<string, mixed> */
	public function to_array() {
		return array(
			'field_name' => $this->field_name,
			'strategy'   => $this->strategy,
			'path'       => $this->path,
			'line'       => $this->line,
			'expression' => $this->expression,
			'root'       => $this->root,
		);
	}

	/** @param mixed[] $values */
	private function assert_non_empty_values( array $values ) {
		foreach ( $values as $value ) {
			if ( '' === trim( (string) $value ) ) {
				throw new RuntimeException( 'Code usage reference values cannot be empty.' );
			}
		}
	}
}
