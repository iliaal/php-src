--TEST--
PDO SQLite: core errors do not expose stale driver diagnostics
--EXTENSIONS--
pdo_sqlite
--FILE--
<?php
$db = new PDO('sqlite::memory:');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
$db->exec('CREATE TABLE t (id INTEGER PRIMARY KEY)');
$db->exec('INSERT INTO t VALUES (1)');
$db->exec('INSERT INTO t VALUES (1)');

echo "driver error:\n";
var_export($db->errorInfo());
echo "\ncore error:\n";
@$db->getAttribute(PDO::ATTR_FETCH_TABLE_NAMES);
var_export($db->errorInfo());
echo "\ntransaction driver error:\n";
$db->exec('PRAGMA foreign_keys = ON');
$db->exec('CREATE TABLE p (id INTEGER PRIMARY KEY)');
$db->exec('CREATE TABLE c (id INTEGER, p INTEGER REFERENCES p(id))');
$db->beginTransaction();
$db->exec('PRAGMA defer_foreign_keys = ON');
$db->exec('INSERT INTO c VALUES (1, 99)');
@$db->getAttribute(PDO::ATTR_FETCH_TABLE_NAMES);
var_export($db->commit());
echo "\n";
var_export($db->errorInfo());
echo "\n";
?>
--EXPECT--
driver error:
array (
  0 => '23000',
  1 => 19,
  2 => 'UNIQUE constraint failed: t.id',
)
core error:
array (
  0 => 'IM001',
  1 => NULL,
  2 => NULL,
)
transaction driver error:
false
array (
  0 => '23000',
  1 => 19,
  2 => 'FOREIGN KEY constraint failed',
)
