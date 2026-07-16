--TEST--
http wrapper must not build a From header from a from address with control characters
--INI--
allow_url_fopen=1
--SKIPIF--
<?php
require 'server.inc'; http_server_skipif();
?>
--FILE--
<?php
require 'server.inc';

$responses = [
    "data://text/plain,HTTP/1.0 200 OK\r\nContent-Length: 2\r\n\r\nOK",
    "data://text/plain,HTTP/1.0 200 OK\r\nContent-Length: 2\r\n\r\nOK",
];

['pid' => $pid, 'uri' => $uri] = http_server($responses, $output);

ini_set('from', "evil@example.com\r\nX-Injected: yes");
file_get_contents($uri);

ini_set('from', 'me@example.com');
file_get_contents($uri);

http_server_kill($pid);

rewind($output);
$sent = stream_get_contents($output);
var_dump(str_contains($sent, 'X-Injected'));
var_dump(str_contains($sent, 'From: me@example.com'));
?>
--EXPECTF--
Warning: file_get_contents(): Cannot construct From header in %s on line %d
bool(false)
bool(true)
