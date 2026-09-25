--TEST--
PDO ODBC does not rewrite named placeholders inside bracketed identifiers
--EXTENSIONS--
pdo_odbc
--SKIPIF--
<?php
require __DIR__ . '/config.inc';
try {
    new PDO(PDO_ODBC_SQLITE_DSN);
} catch (PDOException $e) {
    die("skip requires the SQLite3 ODBC driver");
}
?>
--FILE--
<?php
require __DIR__ . '/config.inc';
$pdo = new PDO(PDO_ODBC_SQLITE_DSN);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->exec('CREATE TABLE t ([:col] INTEGER, id INTEGER)');
$pdo->exec('INSERT INTO t ([:col], id) VALUES (42, 1)');
$stmt = $pdo->prepare('SELECT [:col] FROM t WHERE id = :id');
$stmt->execute(['id' => 1]);
var_dump($stmt->fetchColumn());
?>
--EXPECT--
string(2) "42"
