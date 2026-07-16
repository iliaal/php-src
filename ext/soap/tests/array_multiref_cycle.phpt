--TEST--
SoapServer: SOAP-ENC array multi-ref cycle must not stack-overflow
--EXTENSIONS--
soap
--FILE--
<?php
class S {
	function test($a) {
		return is_array($a) ? 'ok' : 'bad';
	}
}

$server = new SoapServer(null, [
	'uri' => 'http://test',
	'soap_version' => SOAP_1_1,
]);
$server->setClass(S::class);
$server->handle(<<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<SOAP-ENV:Envelope
	xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/"
	xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/"
	xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
	xmlns:xsd="http://www.w3.org/2001/XMLSchema"
	SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/">
	<SOAP-ENV:Body>
		<ns1:test xmlns:ns1="http://test">
			<param0 id="ref1" xsi:type="SOAP-ENC:Array" SOAP-ENC:arrayType="xsd:anyType[1]">
				<item href="#ref1"/>
			</param0>
		</ns1:test>
	</SOAP-ENV:Body>
</SOAP-ENV:Envelope>
XML);
?>
--EXPECT--
<?xml version="1.0" encoding="UTF-8"?>
<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns1="http://test" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/" SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"><SOAP-ENV:Body><ns1:testResponse><return xsi:type="xsd:string">ok</return></ns1:testResponse></SOAP-ENV:Body></SOAP-ENV:Envelope>
