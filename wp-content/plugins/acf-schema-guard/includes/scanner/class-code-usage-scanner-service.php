<?php
namespace AcfSchemaGuard\Scanner;
if ( ! defined( 'ABSPATH' ) ) { exit; }
final class CodeUsageScannerService {
	private $scanners;
	public function __construct( array $scanners ) {
		$this->scanners = $scanners;
	}
	public function scan( array $source_roots ) {
		$references = array();
		foreach ( $this->scanners as $scanner ) {
			if ( ! $scanner instanceof CodeUsageScanner ) {
				continue;
			}
			foreach ( $scanner->scan( $source_roots ) as $reference ) {
				if ( $reference instanceof CodeUsageReference ) {
					$references[ implode( '|', $reference->to_array() ) ] = $reference;
				}
			}
		}
		ksort( $references, SORT_STRING );
		return array_values( $references );
	}
	public function dynamic_references( array $source_roots ) {
		$references = array();
		foreach ( $this->scanners as $scanner ) {
			if ( ! $scanner instanceof CodeUsageScanner || ! method_exists( $scanner, 'dynamic_references' ) ) {
				continue;
			}
			foreach ( $scanner->dynamic_references( $source_roots ) as $reference ) {
				if ( $reference instanceof DynamicCodeUsageReference ) {
					$references[ implode( '|', $reference->to_array() ) ] = $reference;
				}
			}
		}
		ksort( $references, SORT_STRING );
		return array_values( $references );
	}
}
