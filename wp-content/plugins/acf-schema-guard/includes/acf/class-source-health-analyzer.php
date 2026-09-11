<?php
/**
 * Classifies independently supplied ACF database and Local JSON groups.
 *
 * @package ACFSchemaGuard
 */

namespace AcfSchemaGuard\Acf;

use AcfSchemaGuard\Schema\CanonicalValue;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class SourceHealthAnalyzer {
	/**
	 * @param array[] $database_groups Canonical groups loaded from WordPress.
	 * @param array[] $json_groups     Canonical groups loaded from Local JSON.
	 * @return SourceHealthReport
	 */
	public function analyze( array $database_groups, array $json_groups ) {
		$database_groups = $this->groups_by_key( $database_groups );
		$json_groups     = $this->groups_by_key( $json_groups );
		$keys            = array_unique( array_merge( array_keys( $database_groups ), array_keys( $json_groups ) ) );
		$findings        = array();

		sort( $keys, SORT_STRING );

		foreach ( $keys as $key ) {
			$database_group = isset( $database_groups[ $key ] ) ? $database_groups[ $key ] : null;
			$json_group     = isset( $json_groups[ $key ] ) ? $json_groups[ $key ] : null;
			$title          = $this->title( $database_group, $json_group );

			$findings[] = new SourceHealthFinding(
				$key,
				$title,
				$this->status( $database_group, $json_group ),
				$database_group,
				$json_group,
				$this->direction( $database_group, $json_group )
			);
		}

		return new SourceHealthReport( true, $findings );
	}

	private function direction( $database_group, $json_group ) {
		if ( null === $database_group || null === $json_group || ! isset( $database_group['_source_modified'], $json_group['_source_modified'] ) ) { return 'unknown'; }
		if ( (int) $json_group['_source_modified'] > (int) $database_group['_source_modified'] ) { return 'json_newer'; }
		if ( (int) $json_group['_source_modified'] < (int) $database_group['_source_modified'] ) { return 'database_newer'; }
		return 'equal';
	}

	/**
	 * @param array[] $groups Candidate canonical groups.
	 * @return array<string, array>
	 */
	private function groups_by_key( array $groups ) {
		$groups_by_key = array();

		foreach ( $groups as $group ) {
			if ( ! is_array( $group ) || empty( $group['key'] ) ) {
				continue;
			}

			$groups_by_key[ (string) $group['key'] ] = CanonicalValue::normalize( $group );
		}

		return $groups_by_key;
	}

	/**
	 * @param array|null $database_group Canonical database group.
	 * @param array|null $json_group     Canonical Local JSON group.
	 * @return string
	 */
	private function status( $database_group, $json_group ) {
		if ( null === $database_group ) {
			return SourceHealthFinding::STATUS_JSON_ONLY;
		}

		if ( null === $json_group ) {
			return SourceHealthFinding::STATUS_DATABASE_ONLY;
		}

		return $this->schema_group( $database_group ) === $this->schema_group( $json_group ) ? SourceHealthFinding::STATUS_ALIGNED : SourceHealthFinding::STATUS_DIVERGENT;
	}

	private function schema_group( array $group ) {
		unset( $group['_source_modified'] );

		return CanonicalValue::normalize( $this->normalize_source_value( $group ) );
	}

	/**
	 * Removes ACF runtime metadata and canonicalizes equivalent empty defaults.
	 *
	 * @param mixed       $value Source value.
	 * @param string|null $key   Parent array key.
	 * @return mixed
	 */
	private function normalize_source_value( $value, $key = null ) {
		if ( ! is_array( $value ) ) {
			return 'default_value' === $key && null === $value ? '' : $value;
		}

		foreach ( $value as $child_key => $child_value ) {
			if ( in_array( $child_key, array( '_valid', 'parent_repeater' ), true ) ) {
				unset( $value[ $child_key ] );
				continue;
			}

			$value[ $child_key ] = $this->normalize_source_value( $child_value, $child_key );

			if ( $this->is_empty_runtime_default( $child_key, $value[ $child_key ] ) ) {
				unset( $value[ $child_key ] );
			}
		}

		if ( isset( $value['type'], $value['settings'] ) && 'image' === $value['type'] && is_array( $value['settings'] ) && ! isset( $value['settings']['preview_size'] ) ) {
			$value['settings']['preview_size'] = 'medium';
		}

		return $value;
	}

	/**
	 * @param string $key   Source key.
	 * @param mixed  $value Source value.
	 * @return bool
	 */
	private function is_empty_runtime_default( $key, $value ) {
		if ( in_array( $key, array( 'class', 'aria-label' ), true ) ) {
			return '' === $value;
		}

		if ( 'wrapper' !== $key || ! is_array( $value ) ) {
			return false;
		}

		return array() === array_filter( $value, static function ( $item ) {
			return '' !== $item;
		} );
	}

	/**
	 * @param array|null $database_group Canonical database group.
	 * @param array|null $json_group     Canonical Local JSON group.
	 * @return string
	 */
	private function title( $database_group, $json_group ) {
		$group = null !== $database_group ? $database_group : $json_group;

		return isset( $group['title'] ) ? (string) $group['title'] : '';
	}
}
