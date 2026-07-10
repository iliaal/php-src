--TEST--
GH-22667 (Heap buffer over-read when a column value exceeds the bound buffer)
--EXTENSIONS--
pdo_odbc
--SKIPIF--
<?php
$file = tempnam(sys_get_temp_dir(), "gh22667");
try {
    $pdo = new PDO("odbc:Driver=SQLite3;Database=$file");
} catch (PDOException $e) {
    @unlink($file);
    die("skip requires the SQLite3 ODBC driver");
}
?>
--FILE--
<?php
$file = tempnam(sys_get_temp_dir(), "gh22667");
$pdo = new PDO("odbc:Driver=SQLite3;Database=$file");

// The SQLite3 driver reports a 255 byte display size for a computed column, so
// the short-bound buffer holds at most 255 bytes while the value is far longer.
// A conforming driver truncates into the buffer but reports the full length; the
// returned string must stay within the buffer, not over-read past it.
$stmt = $pdo->query("SELECT printf('%.*c', 4096, 'A') AS data");
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$s = $row['data'];

echo "clamped to buffer: "; var_dump(strlen($s) < 4096);
echo "only value bytes:  "; var_dump(strlen($s) === substr_count($s, 'A'));

@unlink($file);
?>
--EXPECT--
clamped to buffer: bool(true)
only value bytes:  bool(true)
