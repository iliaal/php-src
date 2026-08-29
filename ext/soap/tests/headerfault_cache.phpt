--TEST--
WSDL cache corruption when soap:header has headerfaults
--EXTENSIONS--
soap
--INI--
soap.wsdl_cache_enabled=1
--FILE--
<?php
$dir = sys_get_temp_dir() . '/wsdlcache' . getmypid();
@mkdir($dir);
ini_set('soap.wsdl_cache_dir', $dir);

$options = ['cache_wsdl' => WSDL_CACHE_DISK];

$c1 = new SoapClient(__DIR__ . '/headerfault_cache.wsdl', $options);
var_dump($c1->__getFunctions());

$c2 = new SoapClient(__DIR__ . '/headerfault_cache.wsdl', $options);
var_dump($c2->__getFunctions());

foreach (glob($dir . '/*') as $f) {
    @unlink($f);
}
@rmdir($dir);
echo "ok\n";
?>
--EXPECT--
array(1) {
  [0]=>
  string(32) "string testHeader(string $param)"
}
array(1) {
  [0]=>
  string(32) "string testHeader(string $param)"
}
ok
