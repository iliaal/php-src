--TEST--
HTTP wrapper must not forward Authorization/Cookie on cross-host redirect
--INI--
allow_url_fopen=1
--FILE--
<?php
$serverCode = <<<'CODE'
$server = stream_socket_server("tcp://127.0.0.1:0", $errno, $errstr);
phpt_notify_server_start($server);

// First hop: redirect to second port embedded in query... use two accepts
// Port1 redirects to port2 via absolute Location
$conn = stream_socket_accept($server);
$req = '';
while (!str_ends_with($req, "\r\n\r\n")) {
    $req .= fread($conn, 1024);
}
// Second server on another port
$server2 = stream_socket_server("tcp://127.0.0.1:0", $e2, $e2s);
preg_match('/:(\d+)$/', stream_socket_get_name($server2, false), $m);
$port2 = (int)$m[1];
fwrite($conn, "HTTP/1.0 302 Found\r\nLocation: http://127.0.0.1:{$port2}/target\r\nContent-Length: 0\r\n\r\n");
fclose($conn);

$conn2 = stream_socket_accept($server2);
$req2 = '';
while (!str_ends_with($req2, "\r\n\r\n")) {
    $req2 .= fread($conn2, 1024);
}
fwrite($conn2, "HTTP/1.0 200 OK\r\nContent-Type: text/plain\r\n\r\n" . base64_encode($req2));
fclose($conn2);
fclose($server2);
CODE;

$clientCode = <<<'CODE'
$ctx = stream_context_create([
    'http' => [
        'header' => "Authorization: Bearer secret-token\r\nCookie: sess=abc\r\n",
        'follow_location' => 1,
        'max_redirects' => 5,
    ],
]);
$raw = base64_decode(file_get_contents('http://{{ ADDR }}/start', false, $ctx));
echo (str_contains($raw, 'Authorization:') ? 'FAIL auth' : 'OK auth'), "\n";
echo (str_contains($raw, 'Cookie:') ? 'FAIL cookie' : 'OK cookie'), "\n";
echo (str_contains($raw, 'GET /target') ? 'OK path' : 'FAIL path'), "\n";
CODE;

include sprintf('%s/../../../openssl/tests/ServerClientTestCase.inc', __DIR__);
ServerClientTestCase::getInstance()->run($clientCode, $serverCode);
?>
--EXPECT--
OK auth
OK cookie
OK path
