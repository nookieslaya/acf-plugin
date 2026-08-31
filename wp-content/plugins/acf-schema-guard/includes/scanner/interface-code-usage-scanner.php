<?php
namespace AcfSchemaGuard\Scanner;
if ( ! defined( 'ABSPATH' ) ) { exit; }
interface CodeUsageScanner { public function strategy(); public function scan( array $source_roots ); }
