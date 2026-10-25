--TEST--
SoapServer restores its global state before bailing out
--EXTENSIONS--
soap
--FILE--
<?php

function fatalError(): never
{
    trigger_error('server error', E_USER_ERROR);
}

$server = new SoapServer(null, ['uri' => 'urn:test']);
$server->addFunction('fatalError');

register_shutdown_function(function () use ($server): void {
    echo "shutdown\n";
    try {
        $server->addSoapHeader(new SoapHeader('urn:test', 'test'));
    } catch (Throwable $e) {
        echo $e::class, ': ', $e->getMessage(), "\n";
    }
    set_error_handler(function (int $errno, string $message): bool {
        if ($errno !== E_DEPRECATED) {
            echo $message, "\n";
        }
        return true;
    });
    trigger_error('shutdown error', E_USER_ERROR);
    echo "continued\n";
});

$server->handle(<<<'XML'
<?xml version="1.0"?>
<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/">
  <SOAP-ENV:Body>
    <ns1:fatalError xmlns:ns1="urn:test"/>
  </SOAP-ENV:Body>
</SOAP-ENV:Envelope>
XML);

echo "unreachable\n";
?>
--EXPECTF--
<?xml version="1.0" encoding="UTF-8"?>
<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/"><SOAP-ENV:Body><SOAP-ENV:Fault><faultcode>SOAP-ENV:Server</faultcode><faultstring>server error</faultstring></SOAP-ENV:Fault></SOAP-ENV:Body></SOAP-ENV:Envelope>
shutdown
Error: SoapServer::addSoapHeader() may be called only during SOAP request processing
shutdown error
continued
