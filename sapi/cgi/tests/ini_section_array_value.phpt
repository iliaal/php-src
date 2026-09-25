--TEST--
PATH/HOST INI sections reject non-string values during activation
--SKIPIF--
<?php
include 'skipif.inc';
?>
--FILE--
<?php
include "include.inc";

$cgi = get_cgi_path();
$script = __DIR__ . '/ini_section_array_value.php';
$ini = __DIR__ . '/ini_section_array_value.ini';

file_put_contents($script, '<?php
var_dump(ini_get("memory_limit"), ini_get("precision"));
');

file_put_contents($ini, sprintf(<<<'INI'
[PATH=%s]
memory_limit = 17M
array_value[] = invalid

[HOST=example.test]
precision = 7
array_value[] = invalid
INI, __DIR__));
$env = getenv();
$env['REDIRECT_STATUS'] = '1';
$env['REQUEST_METHOD'] = 'GET';
$env['SCRIPT_FILENAME'] = $script;
$env['PATH_TRANSLATED'] = $script;
$env['SERVER_NAME'] = 'example.test';

$process = proc_open([$cgi, '-q', '-c', $ini, '-f', $script], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, __DIR__, $env);
var_dump(stream_get_contents($pipes[1]));
var_dump(stream_get_contents($pipes[2]));
var_dump(proc_close($process));
?>
--CLEAN--
<?php
@unlink(__DIR__ . '/ini_section_array_value.php');
@unlink(__DIR__ . '/ini_section_array_value.ini');
?>
--EXPECTF--
string(%d) "X-Powered-By: PHP/%s
Content-type: text/html; charset=UTF-8

string(3) "17M"
string(1) "7"
"
string(0) ""
int(0)
