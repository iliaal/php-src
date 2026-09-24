--TEST--
Persistent DBLib handles preserve error string ownership across requests
--EXTENSIONS--
pdo_dblib
--SKIPIF--
<?php
require __DIR__ . '/config.inc';
getDbConnection();
?>
--FILE--
<?php
require __DIR__ . '/../../../sapi/cli/tests/php_cli_server.inc';

$config = var_export(__DIR__ . '/config.inc', true);
$code = <<<'PHP'
require %s;
[$dsn, $user, $pass] = getCredentials();
$db = new PDO($dsn, $user, $pass, [
    PDO::ATTR_PERSISTENT => true,
    PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT,
]);
$db->exec('select * from pdo_dblib_missing_table');
echo $db->errorCode() === '00000' ? "no error\n" : "error\n";
PHP;
$code = sprintf($code, $config);

$extension_dir = realpath(ini_get('extension_dir')) ?: ini_get('extension_dir');
$extension_suffix = PHP_OS_FAMILY === 'Windows' ? '.dll' : '.so';
$server_args = [];
if (is_file($extension_dir . DIRECTORY_SEPARATOR . 'pdo_dblib' . $extension_suffix)) {
	$server_args = [
		'-d',
		'extension_dir=' . $extension_dir,
		'-d',
		'extension=pdo_dblib',
	];
}
php_cli_server_start($code, 'index.php', $server_args);

for ($i = 0; $i < 2; $i++) {
    $response = @file_get_contents('http://' . PHP_CLI_SERVER_ADDRESS . '/index.php');
    echo $response === "error\n" ? "error\n" : "unexpected response\n";
}
?>
--EXPECT--
error
error
