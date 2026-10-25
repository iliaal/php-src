--TEST--
ftps:// stream must fail if the server refuses private data protection
--EXTENSIONS--
openssl
--SKIPIF--
<?php
if (array_search('ftp', stream_get_wrappers()) === false) die("skip ftp wrapper not available.");
if (!function_exists('pcntl_fork')) die("skip pcntl_fork() not available.");
?>
--FILE--
<?php
$ssl = $refuse_prot_p = true;
require __DIR__ . '/../../../ftp/tests/server.inc';

$path = 'ftps://127.0.0.1:' . $port . '/fget';
$context = stream_context_create([
    'ssl' => ['cafile' => __DIR__ . '/../../../ftp/tests/cert.pem'],
]);

var_dump(file_get_contents($path, false, $context));
?>
--EXPECTF--
Warning: file_get_contents(): Failed to open stream: Server doesn't support private data protection. in %s on line %d
bool(false)
