<?php
/**
 * Reads ACF references from the configured source roots for the current request.
 *
 * @package ACFSchemaGuard
 */

namespace AcfSchemaGuard\Scanner;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class CurrentCodeUsageService {
	/** @var CodeUsageScannerService */
	private $scanner;

	/** @var ScannerConfiguration */
	private $configuration;

	public function __construct( CodeUsageScannerService $scanner, ScannerConfiguration $configuration ) {
		$this->scanner       = $scanner;
		$this->configuration = $configuration;
	}

	/** @return CodeUsageReference[] */
	public function references() {
		return $this->scanner->scan( $this->roots() );
	}

	/** @return DynamicCodeUsageReference[] */
	public function dynamic_references() {
		return $this->scanner->dynamic_references( $this->roots() );
	}

	/** @return string[] */
	public function roots() {
		return $this->configuration->roots();
	}
}
