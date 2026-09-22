<?php
/**
 * Builds value-free repair plans for direct ACF field renames.
 *
 * @package ACFSchemaGuard
 */

namespace AcfSchemaGuard\Impact;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class SafeRenamePlanner {
	const RECORD_LIMIT = 20;

	private $repository;

	public function __construct( RenameDryRunRepository $repository ) {
		$this->repository = $repository;
	}

	public function analyze( array $changes, array $references ) {
		$plans = array();
		foreach ( $changes as $change ) {
			if ( ! $this->is_rename( $change ) ) {
				continue;
			}
			$old_name = (string) $change['before']['name'];
			$new_name = (string) $change['after']['name'];
			$code     = $this->references( $references, $old_name );
			if ( ! empty( $change['context']['ancestors'] ) ) {
				$plans[] = $this->plan( $change, $old_name, $new_name, $code, array( 'status' => 'not_supported', 'old_record_count' => 0, 'conflict_count' => 0, 'migration_candidate_count' => 0, 'records' => array() ) );
				continue;
			}
			$evidence = $this->repository->inspect( $old_name, $new_name, self::RECORD_LIMIT );
			$old_count = max( 0, isset( $evidence['old_record_count'] ) ? (int) $evidence['old_record_count'] : 0 );
			$conflicts = min( $old_count, max( 0, isset( $evidence['conflict_count'] ) ? (int) $evidence['conflict_count'] : 0 ) );
			$status    = 0 === $old_count ? 'no_records' : ( 0 < $conflicts ? 'conflicts' : 'ready' );
			$plans[]   = $this->plan( $change, $old_name, $new_name, $code, array( 'status' => $status, 'old_record_count' => $old_count, 'conflict_count' => $conflicts, 'migration_candidate_count' => max( 0, $old_count - $conflicts ), 'records' => isset( $evidence['records'] ) && is_array( $evidence['records'] ) ? array_slice( $evidence['records'], 0, self::RECORD_LIMIT ) : array() ) );
		}
		return $plans;
	}

	private function is_rename( $change ) {
		return is_array( $change ) && 'modified' === ( isset( $change['kind'] ) ? $change['kind'] : '' ) && 'field' === ( isset( $change['node_type'] ) ? $change['node_type'] : '' ) && isset( $change['before'], $change['after'] ) && is_array( $change['before'] ) && is_array( $change['after'] ) && isset( $change['before']['name'], $change['after']['name'] ) && '' !== $change['before']['name'] && '' !== $change['after']['name'] && $change['before']['name'] !== $change['after']['name'];
	}

	private function references( array $references, $field_name ) {
		$matches = array();
		foreach ( $references as $reference ) {
			$data = is_object( $reference ) && method_exists( $reference, 'to_array' ) ? $reference->to_array() : $reference;
			if ( is_array( $data ) && isset( $data['field_name'] ) && $field_name === $data['field_name'] ) {
				$matches[] = $data;
			}
		}
		return $matches;
	}

	private function plan( array $change, $old_name, $new_name, array $references, array $dry_run ) {
		return array( 'change' => $change, 'old_name' => $old_name, 'new_name' => $new_name, 'code_references' => $references, 'dry_run' => $dry_run );
	}
}
