--TEST--
CLI server rejects pipelined requests in the same read
--SKIPIF--
<?php
include 'skipif.inc';
?>
--FILE--
<?php
include 'php_cli_server.inc';
php_cli_server_start('echo "request executed";');

$socket = php_cli_server_connect();
$request = "GET /first.php?one HTTP/1.1\r\nHost: localhost\r\n\r\n"
    . "GET /second.php?two HTTP/1.1\r\nHost: localhost\r\n\r\n";
fwrite($socket, $request);
var_dump(stream_get_contents($socket));
fclose($socket);
?>
--EXPECT--
string(0) ""
