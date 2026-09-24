--TEST--
PDO_Firebird: getColumnMeta() rejects an out-of-range column index
--EXTENSIONS--
pdo_firebird
--SKIPIF--
<?php require('skipif.inc'); ?>
--XLEAK--
A bug in firebird causes a memory leak when calling `isc_attach_database()`.
See https://github.com/FirebirdSQL/firebird/issues/7849
--FILE--
<?php
require "testdb.inc";

$dbh = getDbConnection();
$stmt = $dbh->query('SELECT 1 AS one FROM rdb$database');

var_dump($stmt->getColumnMeta(1));
var_dump(array_keys($stmt->getColumnMeta(0)));
?>
--EXPECT--
bool(false)
array(4) {
  [0]=>
  string(8) "pdo_type"
  [1]=>
  string(4) "name"
  [2]=>
  string(3) "len"
  [3]=>
  string(9) "precision"
}
