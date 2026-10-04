--TEST--
ext/sockets - PHP_NORMAL_READ returns on EAGAIN for non-blocking sockets
--EXTENSIONS--
sockets
pcntl
posix
--SKIPIF--
<?php
if (!function_exists('pcntl_fork') || !function_exists('pcntl_waitpid') || !function_exists('posix_kill')) {
    die('skip pcntl and posix functions are required');
}
?>
--FILE--
<?php
$domain = PHP_OS_FAMILY === 'Windows' ? AF_INET : AF_UNIX;
$sockets = [];
socket_create_pair($domain, SOCK_STREAM, 0, $sockets);
$pid = pcntl_fork();
if ($pid === -1) {
    die('fork failed');
}
if ($pid === 0) {
    $data = socket_read($sockets[0], 1024, PHP_NORMAL_READ);
    exit($data === '' ? 0 : 1);
}

$status = 0;
for ($i = 0; $i < 100; $i++) {
    $result = pcntl_waitpid($pid, $status, WNOHANG);
    if ($result === $pid) {
        var_dump(pcntl_wexitstatus($status));
        exit(0);
    }
    usleep(10000);
}

posix_kill($pid, SIGKILL);
pcntl_waitpid($pid, $status);
var_dump('socket_read() did not return');
exit(1);
?>
--EXPECT--
int(0)
