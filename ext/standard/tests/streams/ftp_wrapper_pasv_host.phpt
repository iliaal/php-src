--TEST--
FTP stream wrapper ignores a PASV host different from the control host
--SKIPIF--
<?php
if (!in_array('ftp', stream_get_wrappers(), true)) die("skip ftp wrapper not available");
if (!function_exists('pcntl_fork')) die("skip pcntl_fork() not available");
?>
--FILE--
<?php
$pasv_host = '127,0,0,2';
require __DIR__ . "/../../../ftp/tests/server.inc";

var_dump(file_get_contents("ftp://127.0.0.1:$port/fget"));
?>
--EXPECT--
string(14) "BINARYFooBar
"
