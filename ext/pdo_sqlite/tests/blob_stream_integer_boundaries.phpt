--TEST--
PDO SQLite BLOB stream integer boundaries
--EXTENSIONS--
pdo_sqlite
--FILE--
<?php
$db = new Pdo\Sqlite('sqlite::memory:');
$db->exec('CREATE TABLE test (id INTEGER PRIMARY KEY, data BLOB)');
$db->exec("INSERT INTO test VALUES (1, 'hello')");

$stream = $db->openBlob('test', 'data', 1);

var_dump(fseek($stream, PHP_INT_MAX, SEEK_SET));
var_dump(ftell($stream));
var_dump(rewind($stream));
var_dump(fseek($stream, 1));
var_dump(fseek($stream, PHP_INT_MAX, SEEK_CUR));
var_dump(ftell($stream));
var_dump(rewind($stream));
var_dump(fseek($stream, PHP_INT_MIN, SEEK_CUR));
var_dump(ftell($stream));
var_dump(fseek($stream, PHP_INT_MIN, SEEK_END));
var_dump(ftell($stream));
var_dump(fseek($stream, -5, SEEK_END));
var_dump(ftell($stream));
var_dump(fread($stream, 6));
var_dump(fseek($stream, -1, SEEK_SET));
var_dump(ftell($stream));

fclose($stream);
$stream = $db->openBlob('test', 'data', 1, 'main', Pdo\Sqlite::OPEN_READWRITE);
var_dump(fseek($stream, -5, SEEK_END));
var_dump(fwrite($stream, 'HELLO'));
var_dump(feof($stream));
?>
--EXPECT--
int(-1)
bool(false)
bool(true)
int(0)
int(-1)
bool(false)
bool(true)
int(-1)
bool(false)
int(-1)
bool(false)
int(0)
int(0)
string(5) "hello"
int(-1)
int(5)
int(0)
int(5)
bool(true)
