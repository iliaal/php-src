--TEST--
proc_open() closes descriptor 0 when setup fails after pipe allocation
--SKIPIF--
<?php
if (!function_exists("proc_open")) {
    die("skip proc_open() unavailable");
}
if (!@is_dir("/proc/self/fd")) {
    die("skip requires /proc/self/fd");
}
?>
--FILE--
<?php
fclose(STDIN);
var_dump(@proc_open("true", [0 => ["pipe", "w"], 1 => ["bogus_type"]], $pipes));
$fd = fopen("/dev/null", "r");
var_dump(readlink("/proc/self/fd/0"));
fclose($fd);
?>
--EXPECT--
bool(false)
string(9) "/dev/null"
