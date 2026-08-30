<?php
/**
 * Deterministic value canonicalization for normalized schema contracts.
 *
 * @package ACFSchemaGuard
 */

namespace AcfSchemaGuard\Schema;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Recursively sorts maps while retaining the order of list values.
 */
final class CanonicalValue {
	/**
	 * Canonicalizes arrays and scalar values.
	 *
	 * @param mixed $value Value to canonicalize.
	 * @return mixed
	 */
	public static function normalize( $value ) {
		if ( ! is_array( $value ) ) {
			return $value;
		}

		foreach ( $value as $key => $item ) {
			$value[ $key ] = self::normalize( $item );
		}

		if ( ! self::is_list( $value ) ) {
			ksort( $value, SORT_STRING );
		}

		return $value;
	}

	/**
	 * Determines whether an array is a sequential list.
	 *
	 * @param array $value Candidate array.
	 * @return bool
	 */
	private static function is_list( array $value ) {
		if ( array() === $value ) {
			return true;
		}

		return array_keys( $value ) === range( 0, count( $value ) - 1 );
	}
}
