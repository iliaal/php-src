--TEST--
PDO MySQL native prepared statement recovers after CR_NEW_STMT_METADATA
--EXTENSIONS--
pdo_mysql
--SKIPIF--
<?php
require_once __DIR__ . '/inc/mysql_pdo_test.inc';
MySQLPDOTest::skip();
if (MySQLPDOTest::isPDOMySQLnd()) die('skip libmysql only');
MySQLPDOTest::skipVersionThanLess(50000);
?>
--FILE--
<?php
require_once __DIR__ . '/inc/mysql_pdo_test.inc';

$pdo = MySQLPDOTest::factory();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

$procedure = 'pdo_mysql_stmt_metadata_recovery';
$pdo->exec("CREATE PROCEDURE {$procedure}() BEGIN SELECT 1; SELECT 1, 2; END");

$stmt = $pdo->prepare("CALL {$procedure}()");
try {
    $stmt->execute();
    echo "unexpected native success\n";
} catch (PDOException $e) {
    echo $e::class, "\n";
}
$stmt->execute();
var_dump($stmt->fetchAll(PDO::FETCH_NUM));
var_dump($stmt->nextRowset());
var_dump($stmt->fetchAll(PDO::FETCH_NUM));
?>
--CLEAN--
<?php
require_once __DIR__ . '/inc/mysql_pdo_test.inc';
$pdo = MySQLPDOTest::factory();
$pdo->exec('DROP PROCEDURE IF EXISTS pdo_mysql_stmt_metadata_recovery');
?>
--EXPECT--
PDOException
array(1) {
  [0]=>
  array(1) {
    [0]=>
    int(1)
  }
}
bool(true)
array(1) {
  [0]=>
  array(2) {
    [0]=>
    int(1)
    [1]=>
    int(2)
  }
}
