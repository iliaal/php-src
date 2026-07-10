--TEST--
GH-22668 (Heap buffer over-read when a column value exceeds the bound buffer)
--EXTENSIONS--
odbc
--SKIPIF--
<?php
$file = tempnam(sys_get_temp_dir(), "gh22668");
$conn = @odbc_connect("Driver=SQLite3;Database=$file", null, null);
if (!$conn) {
    @unlink($file);
    die("skip requires the SQLite3 ODBC driver");
}
?>
--FILE--
<?php
$file = tempnam(sys_get_temp_dir(), "gh22668");
$conn = odbc_connect("Driver=SQLite3;Database=$file", null, null);

// The SQLite3 driver reports a 255 byte display size for a computed column, so
// the bound buffer holds at most 255 bytes while the value is far longer. A
// conforming driver truncates into the buffer but reports the full length; the
// returned string must stay within the buffer, not over-read past it.
$result = odbc_exec($conn, "SELECT printf('%.*c', 4096, 'A') AS data");
$row = odbc_fetch_array($result);
$s = $row['data'];

echo "clamped to buffer: "; var_dump(strlen($s) < 4096);
echo "only value bytes:  "; var_dump(strlen($s) === substr_count($s, 'A'));

odbc_close($conn);
@unlink($file);
?>
--EXPECT--
clamped to buffer: bool(true)
only value bytes:  bool(true)
