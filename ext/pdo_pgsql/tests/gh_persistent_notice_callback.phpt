--TEST--
PDO_PGSQL persistent connection notice callback cleanup
--EXTENSIONS--
pdo
pdo_pgsql
--SKIPIF--
<?php
require __DIR__ . '/config.inc';
require __DIR__ . '/../../../ext/pdo/tests/pdo_test.inc';
PDOTest::skip();
if (!function_exists('proc_open')) {
    die('skip proc_open is required');
}
?>
--FILE--
<?php
require __DIR__ . '/config.inc';
$router = tempnam(sys_get_temp_dir(), 'pdo_pgsql_notice_');
$log = tempnam(sys_get_temp_dir(), 'pdo_pgsql_notice_');
$routerCode = <<<'PHP_ROUTER'
<?php
$dsn = getenv('PDOTEST_DSN');
$user = getenv('PDOTEST_USER');
$pass = getenv('PDOTEST_PASS');
$db = new Pdo\Pgsql($dsn, $user ?: null, $pass ?: null, [PDO::ATTR_PERSISTENT => true]);
if ($_SERVER['REQUEST_URI'] === '/set') {
    $db->setNoticeCallback('test_notice');
    echo 'ok';
} else {
    $db->setNoticeCallback('test_notice');
    $db->exec("DO \$\$ BEGIN RAISE NOTICE 'persistent notice'; END \$\$");
    echo 'ok';
}
function test_notice($message) {
    echo 'notice: ', trim($message), "\n";
}
PHP_ROUTER;
file_put_contents($router, $routerCode);
$phpExecutable = getenv('TEST_PHP_EXECUTABLE') ?: PHP_BINARY;
$ini = trim((string) getenv('TEST_PHP_EXTRA_ARGS'));
$iniArgs = preg_split('/\s+/', $ini);
$iniArgs = array_map(function ($arg) {
	return trim($arg, '\'"');
}, $iniArgs);
$port = random_int(20000, 60000);
$descriptors = [
    0 => ['pipe', 'r'],
    1 => ['file', $log, 'a'],
    2 => ['file', $log, 'a'],
];
$pipes = [];
$process = proc_open([$phpExecutable, '-t', __DIR__, '-n', ...$iniArgs, '-S', "127.0.0.1:$port", $router], $descriptors, $pipes);
if (!is_resource($process)) {
	@unlink($router);
	@unlink($log);
	die("built-in server failed to start\n");
}
fclose($pipes[0]);
$stop = function () use (&$process, $router, $log) {
    if (is_resource($process)) {
        proc_terminate($process);
        proc_close($process);
    }
    @unlink($router);
    @unlink($log);
};
register_shutdown_function($stop);
$ready = false;
for ($i = 0; $i < 100; $i++) {
    $socket = @fsockopen('127.0.0.1', $port, $errno, $errstr, 0.1);
    if ($socket) {
        fclose($socket);
        $ready = true;
        break;
    }
    usleep(10000);
}
if (!$ready) {
    die("built-in server failed to start\n");
}
$context = stream_context_create(['http' => ['timeout' => 5]]);
$request = function ($path) use ($port, $context) {
    $response = file_get_contents("http://127.0.0.1:$port$path", false, $context);
    if ($response === false) {
        die("request failed\n");
    }
    return $response;
};
echo $request('/set'), "\n";
echo $request('/notice'), "\n";
?>
--EXPECT--
ok
notice: persistent notice
ok
