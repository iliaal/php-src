--TEST--
FTP directory stream ignores a PASV host different from the control host
--SKIPIF--
<?php
if (!in_array('ftp', stream_get_wrappers(), true)) die("skip ftp wrapper not available");
if (!function_exists('pcntl_fork')) die("skip pcntl_fork() not available");
?>
--FILE--
<?php
$pasv_host = '127,0,0,2';
require __DIR__ . "/../../../ftp/tests/server.inc";

$dir = opendir("ftp://127.0.0.1:$port/");
var_dump($dir !== false);
if ($dir) {
    closedir($dir);
}
?>
--EXPECT--
bool(true)
