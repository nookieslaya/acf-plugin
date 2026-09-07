<?php
namespace AcfSchemaGuard\Impact;
if ( ! defined( 'ABSPATH' ) ) { exit; }
final class CodeImpact {
	private $change; private $reference; private $severity; private $message;
	public function __construct( array $change, array $reference, $severity, $message ) { $this->change=$change; $this->reference=$reference; $this->severity=(string)$severity; $this->message=(string)$message; }
	public function to_array() { return array( 'change'=>$this->change, 'reference'=>$this->reference, 'severity'=>$this->severity, 'message'=>$this->message ); }
}
