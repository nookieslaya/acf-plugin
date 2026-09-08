<?php
/**
 * Finds supported literal ACF calls in PHP source code.
 *
 * @package ACFSchemaGuard
 */

namespace AcfSchemaGuard\Scanner;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class PhpAcfUsageScanner implements CodeUsageScanner {
	/** @var string[] */
	private $supported_functions = array(
		'get_field',
		'the_field',
		'get_sub_field',
		'the_sub_field',
		'have_rows',
		'get_field_object',
	);

	/** @return string */
	public function strategy() {
		return 'php-acf';
	}

	/**
	 * @param string[] $source_roots Source directories.
	 * @return CodeUsageReference[]
	 */
	public function scan( array $source_roots ) {
		$references = array();

		foreach ( $source_roots as $root ) {
			if ( ! is_string( $root ) || ! is_dir( $root ) ) {
				continue;
			}

			$iterator = new \RecursiveIteratorIterator(
				new \RecursiveDirectoryIterator( $root, \FilesystemIterator::SKIP_DOTS )
			);

			foreach ( $iterator as $file ) {
				if ( ! $file->isFile() || ! is_readable( $file->getPathname() ) || 'php' !== strtolower( $file->getExtension() ) ) {
					continue;
				}

				$references = array_merge( $references, $this->scan_file( $file->getPathname(), $root ) );
			}
		}

		return $references;
	}

	/**
	 * @param string $path Source file path.
	 * @param string $root Source root.
	 * @return CodeUsageReference[]
	 */
	private function scan_file( $path, $root ) {
		$content = file_get_contents( $path );

		if ( false === $content ) {
			return array();
		}

		$tokens     = token_get_all( $content );
		$references = array();

		foreach ( $tokens as $index => $token ) {
			$call = $this->supported_call( $tokens, $index );

			if ( null === $call ) {
				continue;
			}

			$references[] = new CodeUsageReference(
				$this->literal_value( $call['literal'] ),
				$this->strategy(),
				$this->relative_path( $path, $root ),
				$call['line'],
				$call['expression'],
				$root
			);
		}

		return $references;
	}

	/**
	 * @param array $tokens PHP tokens.
	 * @param int   $index Candidate token index.
	 * @return array|null
	 */
	private function supported_call( array $tokens, $index ) {
		$function = $this->function_name( $tokens[ $index ] );

		if ( null === $function || ! in_array( $function['name'], $this->supported_functions, true ) || ! $this->has_global_call_context( $tokens, $index, $function['fully_qualified'] ) ) {
			return null;
		}

		$opening_index = $this->next_significant_index( $tokens, $index );
		if ( null === $opening_index || '(' !== $tokens[ $opening_index ] ) {
			return null;
		}

		$literal_index = $this->next_significant_index( $tokens, $opening_index );
		if ( null === $literal_index || ! is_array( $tokens[ $literal_index ] ) || T_CONSTANT_ENCAPSED_STRING !== $tokens[ $literal_index ][0] ) {
			return null;
		}

		$after_literal = $this->next_significant_index( $tokens, $literal_index );
		if ( null === $after_literal || ! in_array( $tokens[ $after_literal ], array( ',', ')' ), true ) ) {
			return null;
		}

		return array(
			'literal'    => $tokens[ $literal_index ][1],
			'line'       => $function['line'],
			'expression' => $function['expression'] . '(' . $tokens[ $literal_index ][1],
		);
	}

	/**
	 * @param mixed $token PHP token.
	 * @return array|null
	 */
	private function function_name( $token ) {
		if ( ! is_array( $token ) ) {
			return null;
		}

		if ( T_STRING === $token[0] ) {
			return array( 'name' => strtolower( $token[1] ), 'line' => $token[2], 'expression' => $token[1], 'fully_qualified' => false );
		}

		if ( defined( 'T_NAME_FULLY_QUALIFIED' ) && constant( 'T_NAME_FULLY_QUALIFIED' ) === $token[0] && 0 === strpos( $token[1], '\\' ) ) {
			$name = substr( $token[1], 1 );

			if ( false === strpos( $name, '\\' ) ) {
				return array( 'name' => strtolower( $name ), 'line' => $token[2], 'expression' => $token[1], 'fully_qualified' => true );
			}
		}

		return null;
	}

	/**
	 * @param array $tokens PHP tokens.
	 * @param int   $index Function token index.
	 * @param bool  $fully_qualified Whether the name includes the global slash.
	 * @return bool
	 */
	private function has_global_call_context( array $tokens, $index, $fully_qualified ) {
		if ( $fully_qualified ) {
			return true;
		}

		$previous_index = $this->previous_significant_index( $tokens, $index );
		if ( null === $previous_index ) {
			return true;
		}

		$previous = $tokens[ $previous_index ];
		if ( is_array( $previous ) && in_array( $previous[0], $this->disallowed_previous_tokens(), true ) ) {
			return false;
		}

		if ( $this->is_ampersand( $previous ) ) {
			$declaration_index = $this->previous_significant_index( $tokens, $previous_index );

			return null === $declaration_index || ! is_array( $tokens[ $declaration_index ] ) || T_FUNCTION !== $tokens[ $declaration_index ][0];
		}

		if ( '\\' === $previous || ( is_array( $previous ) && T_NS_SEPARATOR === $previous[0] ) ) {
			$qualifier_index = $this->previous_significant_index( $tokens, $previous_index );

			return null === $qualifier_index || ! is_array( $tokens[ $qualifier_index ] ) || T_STRING !== $tokens[ $qualifier_index ][0];
		}

		return true;
	}

	/** @return int[] */
	private function disallowed_previous_tokens() {
		$tokens = array( T_FUNCTION, T_OBJECT_OPERATOR, T_DOUBLE_COLON );

		if ( defined( 'T_NULLSAFE_OBJECT_OPERATOR' ) ) {
			$tokens[] = constant( 'T_NULLSAFE_OBJECT_OPERATOR' );
		}

		return $tokens;
	}

	/** @return int|null */
	private function next_significant_index( array $tokens, $index ) {
		for ( $candidate = $index + 1, $count = count( $tokens ); $candidate < $count; $candidate++ ) {
			if ( ! $this->is_ignored_token( $tokens[ $candidate ] ) ) {
				return $candidate;
			}
		}

		return null;
	}

	/** @return int|null */
	private function previous_significant_index( array $tokens, $index ) {
		for ( $candidate = $index - 1; 0 <= $candidate; $candidate-- ) {
			if ( ! $this->is_ignored_token( $tokens[ $candidate ] ) ) {
				return $candidate;
			}
		}

		return null;
	}

	/** @param mixed $token PHP token. */
	private function is_ignored_token( $token ) {
		return is_array( $token ) && in_array( $token[0], array( T_WHITESPACE, T_COMMENT, T_DOC_COMMENT ), true );
	}

	/**
	 * PHP 8 represents ampersands in declarations as token arrays, while older
	 * versions expose them as a literal character.
	 *
	 * @param mixed $token PHP token.
	 * @return bool
	 */
	private function is_ampersand( $token ) {
		if ( '&' === $token ) {
			return true;
		}

		return is_array( $token ) && '&' === $token[1];
	}

	/** @return string */
	private function literal_value( $literal ) {
		$quote = substr( $literal, 0, 1 );
		$value = substr( $literal, 1, -1 );

		if ( "'" === $quote ) {
			return str_replace( array( "\\'", '\\\\' ), array( "'", '\\' ), $value );
		}

		return stripcslashes( $value );
	}

	/** @return string */
	private function relative_path( $path, $root ) {
		$root = rtrim( $root, DIRECTORY_SEPARATOR );

		return ltrim( substr( $path, strlen( $root ) ), DIRECTORY_SEPARATOR );
	}
}
