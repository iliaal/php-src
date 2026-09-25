--TEST--
PDO ODBC rejects NUL bytes before rewriting SQL parameters
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

foreach (["SELECT 1\0, :value", "SELECT :value\0, 2"] as $query) {
    try {
        $pdo->prepare($query);
        echo "NUL accepted\n";
    } catch (PDOException $e) {
        echo $e::class, ": ", $e->getCode(), ": ",
            str_contains($e->getMessage(), 'NUL byte') ? 'NUL byte' : $e->getMessage(), "\n";
    }
}
?>
--EXPECT--
PDOException: HY000: NUL byte
PDOException: HY000: NUL byte
