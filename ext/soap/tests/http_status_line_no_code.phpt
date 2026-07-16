--TEST--
SoapClient: malformed HTTP status line without status code must not crash
--EXTENSIONS--
soap
--FILE--
<?php
$serverCode = <<<'CODE'
$ctxt = stream_context_create([
	"socket" => [
		"tcp_nodelay" => true,
	],
]);
$server = stream_socket_server(
	"tcp://127.0.0.1:0", $errno, $errstr, STREAM_SERVER_BIND | STREAM_SERVER_LISTEN, $ctxt);
phpt_notify_server_start($server);
$conn = stream_socket_accept($server);
// Drain request headers
while (($line = fgets($conn)) !== false) {
	if ($line === "\r\n" || $line === "\n") {
		break;
	}
}
fwrite($conn, "HTTP/1.1\r\nContent-Type: text/xml\r\nContent-Length: 0\r\n\r\n");
fclose($conn);
CODE;

$clientCode = <<<'CODE'
$client = new SoapClient(null, [
	'location' => 'http://{{ ADDR }}',
	'uri' => 'http://testuri.org',
	'connection_timeout' => 3,
	'exceptions' => true,
]);
try {
	$client->__doRequest('<x/>', 'http://{{ ADDR }}', 'T', 1);
	echo "survived\n";
} catch (SoapFault $e) {
	echo "survived\n";
} catch (Throwable $e) {
	echo get_class($e), "\n";
}
CODE;

include sprintf('%s/../../openssl/tests/ServerClientTestCase.inc', __DIR__);
ServerClientTestCase::getInstance()->run($clientCode, $serverCode);
?>
--EXPECT--
survived
