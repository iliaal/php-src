--TEST--
SoapServer: empty Body with SOAPAction must not crash (RPC)
--EXTENSIONS--
soap
--INI--
soap.wsdl_cache_enabled=0
--SKIPIF--
<?php
if (php_sapi_name() == 'cli') echo 'skip non-CLI SAPI required for --POST-- / php://input';
?>
--POST--
<?xml version="1.0" encoding="UTF-8"?>
<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/">
  <SOAP-ENV:Body/>
</SOAP-ENV:Envelope>
--FILE--
<?php
class T {
	function test() {
		return 'ok';
	}
}

$server = new SoapServer(__DIR__ . '/server025.wsdl', [
	'cache_wsdl' => WSDL_CACHE_NONE,
]);
$server->setClass(T::class);
$_SERVER['HTTP_SOAPACTION'] = '"Test"';
$server->handle();
?>
--EXPECT--
<?xml version="1.0" encoding="UTF-8"?>
<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns1="http://testuri.org"><SOAP-ENV:Body><ns1:testResponse><result>ok</result></ns1:testResponse></SOAP-ENV:Body></SOAP-ENV:Envelope>
