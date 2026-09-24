--TEST--
proc_open() does not spawn a child when adding an invalid cwd to POSIX spawn file actions fails
--SKIPIF--
<?php
if (!function_exists("proc_open")) die("skip proc_open() unavailable");
if (PHP_OS_FAMILY !== "Darwin" && PHP_OS !== "FreeBSD") {
    die("skip this platform does not fail when queuing the chdir action");
}
?>
--FILE--
<?php
$cwd = __DIR__ . '/nonexistent-' . uniqid();
$command = [PHP_BINARY, '-r', 'echo "spawned";'];

$process = proc_open($command, [1 => ['pipe', 'w']], $pipes, $cwd);
var_dump($process);
?>
--EXPECTF--
Warning: proc_open(): posix_spawn_file_actions_addchdir%s failed: %s in %s on line %d
bool(false)
