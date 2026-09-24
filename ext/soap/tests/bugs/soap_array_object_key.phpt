--TEST--
SOAP array iterator with an object key raises TypeError safely
--EXTENSIONS--
soap
--FILE--
<?php
class ObjectKeyIterator implements Iterator
{
    private bool $valid = true;

    public function current(): mixed
    {
        return new stdClass();
    }

    public function key(): mixed
    {
        return new stdClass();
    }

    public function next(): void
    {
        $this->valid = false;
    }

    public function rewind(): void
    {
        $this->valid = true;
    }

    public function valid(): bool
    {
        return $this->valid;
    }
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
    $client->test(new SoapVar(new ObjectKeyIterator(), SOAP_ENC_ARRAY));
} catch (TypeError $e) {
    echo $e::class, ': ', $e->getMessage(), "\n";
}

var_dump($client->requestReached);
?>
--EXPECT--
TypeError: Cannot access offset of type stdClass on array
bool(false)
