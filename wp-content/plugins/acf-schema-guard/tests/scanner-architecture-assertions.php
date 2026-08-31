<?php
define( 'ABSPATH', __DIR__ . '/' );
foreach(array('class-code-usage-reference.php','interface-code-usage-scanner.php','class-code-usage-scanner-service.php') as $file){require_once dirname(__DIR__).'/includes/scanner/'.$file;}
$ref=new \AcfSchemaGuard\Scanner\CodeUsageReference('hero_title','fake','theme/a.php',4,"get_field('hero_title')");
$scanner=new class($ref) implements \AcfSchemaGuard\Scanner\CodeUsageScanner { private $ref; public function __construct($ref){$this->ref=$ref;} public function strategy(){return 'fake';} public function scan(array $roots){return array($this->ref,$this->ref);} };
if(1!==count((new \AcfSchemaGuard\Scanner\CodeUsageScannerService(array($scanner)))->scan(array('theme')))){exit(1);} echo "Scanner architecture assertions passed.\n";
