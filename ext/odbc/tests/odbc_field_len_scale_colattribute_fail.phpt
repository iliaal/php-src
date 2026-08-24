--TEST--
odbc_field_len(), odbc_field_scale() and odbc_field_type() when SQLColAttribute fails
--EXTENSIONS--
odbc
--SKIPIF--
<?php
if (PHP_OS_FAMILY !== 'Linux') {
    die('skip requires LD_PRELOAD interposition');
}
$db = sys_get_temp_dir() . '/php_odbc_sqlcolattr_skip_' . getmypid() . '.db';
touch($db);
$c = @odbc_connect('Driver={SQLite3};Database=' . $db, '', '');
@unlink($db);
if ($c === false) {
    die('skip no usable ODBC SQLite3 driver');
}
$src = sys_get_temp_dir() . '/php_odbc_sqlcolattr_fail_shim.c';
$so = sys_get_temp_dir() . '/php_odbc_sqlcolattr_fail_shim.so';
$tmpso = $so . '.' . getmypid();
file_put_contents($src, <<<'CODE'
#define _GNU_SOURCE
#include <sqltypes.h>
#include <sqlext.h>
#include <dlfcn.h>
static SQLRETURN (*real_colattr)(SQLHSTMT, SQLUSMALLINT, SQLUSMALLINT, SQLPOINTER, SQLSMALLINT, SQLSMALLINT *, SQLLEN *) = 0;
SQLRETURN SQLColAttribute(SQLHSTMT hstmt, SQLUSMALLINT col, SQLUSMALLINT field, SQLPOINTER ptr, SQLSMALLINT buflen, SQLSMALLINT *outlen, SQLLEN *attr)
{
    if (!real_colattr) {
        real_colattr = (SQLRETURN (*)(SQLHSTMT, SQLUSMALLINT, SQLUSMALLINT, SQLPOINTER, SQLSMALLINT, SQLSMALLINT *, SQLLEN *))dlsym(RTLD_NEXT, "SQLColAttribute");
    }
    if (field == SQL_COLUMN_PRECISION || field == SQL_COLUMN_SCALE || field == SQL_COLUMN_TYPE_NAME) {
        return SQL_ERROR;
    }
    return real_colattr(hstmt, col, field, ptr, buflen, outlen, attr);
}
CODE);
exec('cc -shared -fPIC -O2 -o ' . escapeshellarg($tmpso) . ' ' . escapeshellarg($src), $o, $rc);
if ($rc !== 0) {
    die('skip unable to compile LD_PRELOAD interposer');
}
if (!rename($tmpso, $so)) {
    die('skip unable to install LD_PRELOAD interposer');
}
?>
--FILE--
<?php
$so = sys_get_temp_dir() . '/php_odbc_sqlcolattr_fail_shim.so';
$db = sys_get_temp_dir() . '/php_odbc_sqlcolattr_fail_' . uniqid('', true) . '.db';
touch($db);
$childCode = <<<'PHP'
<?php
$conn = odbc_connect('Driver={SQLite3};Database=' . $argv[1], '', '');
if (!$conn) {
    echo "NOCONN\n";
    exit(1);
}
odbc_exec($conn, 'CREATE TABLE t1 (a int, b decimal(10,2))');
odbc_exec($conn, 'INSERT INTO t1 VALUES (1, 2)');
$r = odbc_exec($conn, 'SELECT a, b FROM t1');
var_dump(odbc_field_len($r, 2));
var_dump(odbc_field_scale($r, 2));
var_dump(odbc_field_type($r, 2));
odbc_close($conn);
PHP;
$childFile = sys_get_temp_dir() . '/php_odbc_sqlcolattr_child_' . uniqid('', true) . '.php';
file_put_contents($childFile, $childCode);
$cmd = 'LD_PRELOAD=' . escapeshellarg($so) . ' ' . escapeshellarg(PHP_BINARY)
    . ' -n ' . escapeshellarg($childFile) . ' ' . escapeshellarg($db) . ' 2>&1';
exec($cmd, $out);
echo implode("\n", $out), "\n";
unlink($childFile);
unlink($db);
@unlink($so);
@unlink(sys_get_temp_dir() . '/php_odbc_sqlcolattr_fail_shim.c');
?>
--EXPECTF--
Warning: odbc_field_len(): SQLColAttribute failed for field #2 in %s on line %d
int(0)

Warning: odbc_field_scale(): SQLColAttribute failed for field #2 in %s on line %d
int(0)

Warning: odbc_field_type(): SQLColAttribute failed for field #2 in %s on line %d
bool(false)
