--TEST--
CLI server preserves chunk order after a short write
--EXTENSIONS--
sockets
--SKIPIF--
<?php
include "skipif.inc";
?>
--FILE--
<?php
include "php_cli_server.inc";
php_cli_server_start(null, null);

$fp = php_cli_server_connect();
$socket = socket_import_stream($fp);
socket_set_option($socket, SOL_SOCKET, SO_RCVBUF, 1024);
$uri = '/' . str_repeat('a', 4096);
fwrite($fp, "GET $uri HTTP/1.1\r\nHost: " . PHP_CLI_SERVER_ADDRESS . "\r\n\r\n");
usleep(100000);
$response = stream_get_contents($fp);
$body = substr($response, strpos($response, "\r\n\r\n") + 4);
$content = '<code class="url">' . $uri . '</code>';
$content_position = strpos($body, $content);
$epilogue_position = strpos($body, '</body></html>');
var_dump(
    $content_position !== false
    && $epilogue_position !== false
    && $epilogue_position > $content_position
    && str_ends_with($body, '</body></html>')
);
?>
--EXPECT--
bool(true)
