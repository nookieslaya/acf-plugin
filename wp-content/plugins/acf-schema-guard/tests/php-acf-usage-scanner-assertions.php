<?php
define( 'ABSPATH', __DIR__ . '/' );
require_once dirname(__DIR__).'/includes/scanner/class-code-usage-reference.php';
require_once dirname(__DIR__).'/includes/scanner/interface-code-usage-scanner.php';
require_once dirname(__DIR__).'/includes/scanner/class-php-acf-usage-scanner.php';
$root=sys_get_temp_dir().'/acf-schema-guard-scan-'.uniqid(); mkdir($root); file_put_contents($root.'/fixture.php',"<?php\nget_field('hero');\nthe_field(\"title\");\nget_field(\$dynamic);\n");
$refs=(new \AcfSchemaGuard\Scanner\PhpAcfUsageScanner())->scan(array($root));
if(2!==count($refs)||'hero'!==$refs[0]->to_array()['field_name']||2!==$refs[0]->to_array()['line']){exit(1);} unlink($root.'/fixture.php'); rmdir($root); echo "PHP ACF scanner assertions passed.\n";
