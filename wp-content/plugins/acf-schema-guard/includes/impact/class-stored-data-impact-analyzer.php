<?php
/**
 * Links destructive ACF changes to direct WordPress post-meta evidence.
 *
 * @package ACFSchemaGuard
 */

namespace AcfSchemaGuard\Impact;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class StoredDataImpactAnalyzer {
	const RECORD_LIMIT = 20;

	private $repository;
	private $matcher_factory;

	public function __construct( StoredDataImpactRepository $repository, ?StoredDataImpactMatcherFactory $matcher_factory = null ) {
		$this->repository      = $repository;
		$this->matcher_factory = null === $matcher_factory ? new StoredDataImpactMatcherFactory() : $matcher_factory;
	}

	public function analyze( array $changes ) {
		$impacts = array();

		$seen = array();

		foreach ( $changes as $change ) {
			if ( ! is_array( $change ) ) {
				continue;
			}
			foreach ( $this->matcher_factory->for_change( $change ) as $matcher ) {
				$key = serialize( array( $change, $matcher->to_array() ) );
				if ( isset( $seen[ $key ] ) ) {
					continue;
				}
				$seen[ $key ] = true;
				$evidence     = 'none' === $matcher->query_type() ? array() : $this->repository->find( $matcher, self::RECORD_LIMIT );
				$impacts[] = new StoredDataImpact(
					$change,
					$matcher,
					isset( $evidence['record_count'] ) ? $evidence['record_count'] : 0,
					isset( $evidence['records'] ) && is_array( $evidence['records'] ) ? $evidence['records'] : array()
				);
			}
		}

		return $impacts;
	}

}
