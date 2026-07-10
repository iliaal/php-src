--TEST--
GH-22666 (Heap buffer overflow when an output param value exceeds its maxlen)
--EXTENSIONS--
pdo_odbc
--SKIPIF--
<?php
$file = tempnam(sys_get_temp_dir(), "gh22666");
try {
    $pdo = new PDO("odbc:Driver=SQLite3;Database=$file");
} catch (PDOException $e) {
    @unlink($file);
    die("skip requires the SQLite3 ODBC driver");
}
?>
--FILE--
<?php
$file = tempnam(sys_get_temp_dir(), "gh22666");
$pdo = new PDO("odbc:Driver=SQLite3;Database=$file");

// An INPUT_OUTPUT parameter declares a maxlen of 4, so the output buffer is 4
// bytes, but the runtime value is longer. The copy into that buffer must be
// bounded to the declared capacity, not overflow it.
$value = str_repeat('A', 64);
$stmt = $pdo->prepare('SELECT ?');
$stmt->bindParam(1, $value, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT, 4);
$stmt->execute();

echo "bounded to maxlen: "; var_dump($value);

@unlink($file);
?>
--EXPECT--
bounded to maxlen: string(4) "AAAA"
