--TEST--
PDO MySQL native prepared statement can retry after execute failure
--EXTENSIONS--
pdo_mysql
--SKIPIF--
<?php
require_once __DIR__ . '/inc/mysql_pdo_test.inc';
MySQLPDOTest::skip();
if (MySQLPDOTest::isPDOMySQLnd()) {
    die('skip libmysql is required');
}
?>
--FILE--
<?php
require_once __DIR__ . '/inc/mysql_pdo_test.inc';

$db = MySQLPDOTest::factory();
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->setAttribute(PDO::MYSQL_ATTR_DIRECT_QUERY, false);
$db->exec('CREATE TABLE pdo_mysql_stmt_execute_retry (id INT PRIMARY KEY, value INT NOT NULL)');

$stmt = $db->prepare('INSERT INTO pdo_mysql_stmt_execute_retry (id, value) VALUES (1, ?)');
$value = null;
$stmt->bindParam(1, $value, PDO::PARAM_INT);

try {
    $stmt->execute();
} catch (PDOException $e) {
    echo $e::class, PHP_EOL;
}

$value = 2;
var_dump($stmt->execute());
var_dump($db->query('SELECT value FROM pdo_mysql_stmt_execute_retry')->fetchColumn());
?>
--CLEAN--
<?php
require_once __DIR__ . '/inc/mysql_pdo_test.inc';
$db = MySQLPDOTest::factory();
$db->exec('DROP TABLE IF EXISTS pdo_mysql_stmt_execute_retry');
?>
--EXPECT--
PDOException
bool(true)
string(1) "2"
