--TEST--
PDO Common: Constructor option errors report $options as argument #4
--EXTENSIONS--
pdo
--SKIPIF--
<?php
$dir = getenv('REDIR_TEST_DIR');
if (false == $dir) die('skip no driver');
require_once $dir . 'pdo_test.inc';
PDOTest::skip();
?>
--FILE--
<?php
if (getenv('REDIR_TEST_DIR') === false) putenv('REDIR_TEST_DIR='.__DIR__ . '/../../pdo/tests/');
require_once getenv('REDIR_TEST_DIR') . 'pdo_test.inc';

putenv("PDOTEST_ATTR=" . serialize([PDO::ATTR_ERRMODE => 999]));
foreach ([false, true] as $useConnectMethod) {
    try {
        PDOTest::factory(PDO::class, $useConnectMethod);
    } catch (ValueError $e) {
        echo $e::class, ": ", $e->getMessage(), "\n";
    }
}
?>
--EXPECT--
ValueError: PDO::__construct(): Argument #4 ($options) Error mode must be one of the PDO::ERRMODE_* constants
ValueError: PDO::connect(): Argument #4 ($options) Error mode must be one of the PDO::ERRMODE_* constants
