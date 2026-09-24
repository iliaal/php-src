--TEST--
SOAP array iterator stops when inserting a key throws
--EXTENSIONS--
soap
--FILE--
<?php
set_error_handler(function ($errno, $errstr) {
    throw new Exception($errstr);
});

function gen()
{
    yield 1.5 => new stdClass();
    yield 2 => new stdClass();
}

class TestSoapClient extends SoapClient
{
    public bool $requestReached = false;

    public function __doRequest(
        $request,
        $location,
        $action,
        $version,
        $one_way = false,
    ): ?string {
        $this->requestReached = true;

        return null;
    }
}

$client = new TestSoapClient(null, [
    'location' => 'test://',
    'uri' => 'urn:test',
]);

try {
    $client->test(new SoapVar(gen(), SOAP_ENC_ARRAY));
} catch (Exception $e) {
    echo $e::class, ': ', $e->getMessage(), "\n";
}

var_dump($client->requestReached);
?>
--EXPECT--
Exception: Implicit conversion from float 1.5 to int loses precision
bool(false)
