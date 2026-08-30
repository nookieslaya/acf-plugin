<?php
namespace AcfSchemaGuard\Diff;
if ( ! defined( 'ABSPATH' ) ) { exit; }
final class RiskFinding { private $change; private $severity; private $rationale; public function __construct( SchemaChange $change, $severity, $rationale ) { $this->change=$change; $this->severity=(string)$severity; $this->rationale=(string)$rationale; } public function to_array() { return array( 'change'=>$this->change->to_array(), 'severity'=>$this->severity, 'rationale'=>$this->rationale ); } }
