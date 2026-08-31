<?php
namespace AcfSchemaGuard\Scanner;
if ( ! defined( 'ABSPATH' ) ) { exit; }
final class CodeUsageScannerService {
	private $scanners;
	public function __construct( array $scanners ) { $this->scanners=$scanners; }
	public function scan( array $source_roots ) { $references=array(); foreach($this->scanners as $scanner){if($scanner instanceof CodeUsageScanner){foreach($scanner->scan($source_roots) as $reference){if($reference instanceof CodeUsageReference){$data=$reference->to_array();$references[implode('|',$data)]=$reference;}}}} ksort($references,SORT_STRING); return array_values($references); }
}
