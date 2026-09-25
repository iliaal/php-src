--TEST--
DBA flatfile persistent iterator uses persistent memory
--EXTENSIONS--
dba
--SKIPIF--
<?php
require_once __DIR__ . '/setup/setup_dba_tests.inc';
check_skip('flatfile');

if (PHP_SAPI !== 'cli') {
    die('skip requires CLI SAPI');
}
if (!function_exists('proc_open')) {
    die('skip proc_open() not available');
}
if (!function_exists('stream_socket_client')) {
    die('skip stream_socket_client() not available');
}
?>
--FILE--
<?php
$dbFile = __DIR__ . '/dba_flatfile_persistent.tmp';
$router = __DIR__ . '/dba_flatfile_persistent_router.tmp';
$serverLog = __DIR__ . '/dba_flatfile_persistent_server.tmp';

$db = dba_open($dbFile, 'n', 'flatfile');
$longKey = str_repeat('a', 2048);
dba_insert($longKey, 'first', $db);
dba_insert('second', 'second', $db);
dba_close($db);

file_put_contents($router, '<?php
$db = dba_popen(' . var_export($dbFile, true) . ', "r", "flatfile");
echo strlen(dba_firstkey($db)), "\n";
echo dba_nextkey($db), "\n";
');

$executable = getenv('TEST_PHP_EXECUTABLE') ?: PHP_BINARY;
$command = [$executable, '-n'];
$extensionDir = ini_get('extension_dir');
if (glob($extensionDir . '/dba.*') || glob($extensionDir . '/php_dba.*')) {
    $command[] = '-d';
    $command[] = 'extension_dir=' . $extensionDir;
    $command[] = '-d';
    $command[] = 'extension=dba';
}
$command[] = '-S';
$command[] = 'localhost:0';
$command[] = $router;

$server = proc_open($command, [0 => STDIN, 1 => ['file', $serverLog, 'w'], 2 => ['pipe', 'w']], $pipes, __DIR__);
if (!is_resource($server)) {
    die("failed to start built-in server\n");
}

register_shutdown_function(function () use ($server, $pipes, $dbFile, $router, $serverLog) {
    proc_terminate($server);
    foreach ($pipes as $pipe) {
        if (is_resource($pipe)) {
            fclose($pipe);
        }
    }
    proc_close($server);
    @unlink($dbFile);
    @unlink($router);
    @unlink($serverLog);
});

stream_set_blocking($pipes[2], false);
$address = null;
for ($i = 0; $i < 60; $i++) {
    usleep(50000);
    while (($line = fgets($pipes[2])) !== false) {
        if (preg_match('@Development Server \(http://([^)]+)\) started@', $line, $matches)) {
            $address = $matches[1];
            break 2;
        }
    }
    $status = proc_get_status($server);
    if (!$status['running']) {
        die("built-in server stopped during startup\n");
    }
}
if ($address === null) {
    die("built-in server did not report its address\n");
}

function request(string $address): void {
    $socket = stream_socket_client("tcp://$address", $errno, $error, 5);
    if ($socket === false) {
        throw new RuntimeException("connection failed: $error");
    }
    fwrite($socket, "GET / HTTP/1.0\r\nHost: localhost\r\nConnection: close\r\n\r\n");
    $response = stream_get_contents($socket);
    fclose($socket);

    $separator = strpos($response, "\r\n\r\n");
    if ($separator === false) {
        throw new RuntimeException("invalid response");
    }
    echo substr($response, $separator + 4);
}

request($address);
request($address);
?>
--CLEAN--
<?php
@unlink(__DIR__ . '/dba_flatfile_persistent.tmp');
@unlink(__DIR__ . '/dba_flatfile_persistent_router.tmp');
@unlink(__DIR__ . '/dba_flatfile_persistent_server.tmp');
?>
--EXPECT--
2048
second
2048
second
