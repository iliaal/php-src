--TEST--
GH-22665 (OOB write in pdo_odbc_error when the driver reports a long message)
--EXTENSIONS--
pdo_odbc
--SKIPIF--
<?php
$file = tempnam(sys_get_temp_dir(), "gh22665");
try {
    $pdo = new PDO("odbc:Driver=SQLite3;Database=$file");
} catch (PDOException $e) {
    @unlink($file);
    die("skip requires the SQLite3 ODBC driver");
}
?>
--FILE--
<?php
$file = tempnam(sys_get_temp_dir(), "gh22665");
$pdo = new PDO("odbc:Driver=SQLite3;Database=$file", null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT,
]);

// A failing query with a long identifier makes the driver report a diagnostic
// message length >= the fixed last_err_msg buffer. The terminator write must
// stay inside the buffer.
$pdo->query('SELECT * FROM "' . str_repeat('A', 4096) . '"');
$info = $pdo->errorInfo();

echo "sqlstate: ", $info[0], "\n";
echo "done\n";

@unlink($file);
?>
--EXPECT--
sqlstate: HY000
done
