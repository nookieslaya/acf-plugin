<?php
/**
 * Stores safe code-scanner roots.
 *
 * @package ACFSchemaGuard
 */

namespace AcfSchemaGuard\Scanner;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class ScannerConfiguration {
	const OPTION_NAME = 'acf_schema_guard_scanner_roots';

	public function roots() {
		$roots = get_option( self::OPTION_NAME, array() );
		$roots = is_array( $roots ) ? $roots : array();
		$roots = $this->valid_roots( $roots );

		if ( empty( $roots ) && function_exists( 'get_stylesheet_directory' ) ) {
			$roots = array( get_stylesheet_directory() );
		}

		return $roots;
	}

	public function save( array $roots ) {
		$identifiers = array();

		foreach ( $roots as $root ) {
			$root = trim( (string) $root );
			$path = $this->path_from_identifier( $root );
			if ( false !== $path && $this->is_allowed_root( $path ) ) {
				$identifiers[] = $root;
			}
		}

		return update_option( self::OPTION_NAME, array_values( array_unique( $identifiers ) ) );
	}

	private function valid_roots( array $roots ) {
		$valid = array();

		foreach ( $roots as $root ) {
			$path = $this->path_from_identifier( $root );
			if ( false !== $path && is_dir( $path ) && $this->is_allowed_root( $path ) ) {
				$valid[] = $path;
			}
		}

		$valid = array_values( array_unique( $valid ) );
		sort( $valid, SORT_STRING );

		return $valid;
	}

	private function path_from_identifier( $root ) {
		$root = trim( (string) $root );

		if ( 0 === strpos( $root, 'theme:' ) ) {
			return realpath( WP_CONTENT_DIR . '/themes/' . substr( $root, 6 ) );
		}

		if ( 0 === strpos( $root, 'plugin:' ) ) {
			return realpath( WP_CONTENT_DIR . '/plugins/' . substr( $root, 7 ) );
		}

		return realpath( $root );
	}

	private function is_allowed_root( $path ) {
		$allowed_roots = array( WP_CONTENT_DIR . '/themes', WP_CONTENT_DIR . '/plugins' );

		foreach ( $allowed_roots as $allowed_root ) {
			$allowed_path = realpath( $allowed_root );
			if ( false !== $allowed_path && 0 === strpos( $path, $allowed_path . DIRECTORY_SEPARATOR ) ) {
				return true;
			}
		}

		return false;
	}
}
