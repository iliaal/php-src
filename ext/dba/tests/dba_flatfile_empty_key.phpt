--TEST--
DBA FlatFile deletion and replacement of empty keys preserve other records
--EXTENSIONS--
dba
--SKIPIF--
<?php
require_once __DIR__ . '/setup/setup_dba_tests.inc';
check_skip('flatfile');
?>
--FILE--
<?php
$path = __DIR__ . '/dba_flatfile_empty_key.db';
foreach ([
    'first' => [['', 'left', 'right'], ''],
    'middle' => [['left', '', 'right'], 'original value'],
    'last' => [['left', 'right', ''], "\0binary\nvalue"],
] as $position => [$order, $value]) {
    echo "$position:\n";
    $db = dba_open($path, 'n', 'flatfile');
    foreach ($order as $key) {
        dba_insert($key, $key === '' ? $value : strtoupper($key), $db);
    }
    echo "delete: ";
    var_dump(dba_delete('', $db));
    echo "deleted key: ";
    var_dump(dba_exists('', $db), dba_fetch('', $db));
    dba_close($db);

    $db = dba_open($path, 'w', 'flatfile');
    echo "delete again: ";
    var_dump(dba_delete('', $db));
    echo "other records: ";
    var_dump(dba_fetch('left', $db), dba_fetch('right', $db));
    $keys = [];
    for ($key = dba_firstkey($db); $key !== false && count($keys) < 4; $key = dba_nextkey($db)) {
        $keys[] = $key;
    }
    echo "remaining keys: ";
    var_dump($keys === ['left', 'right'], $key === false);
    echo "reinsert: ";
    var_dump(dba_insert('', $value, $db), dba_fetch('', $db) === $value);
    echo "replace: ";
    var_dump(dba_replace('', 'updated', $db));
    dba_close($db);

    $db = dba_open($path, 'r', 'flatfile');
    echo "reopened: ";
    var_dump(dba_fetch('', $db), dba_fetch('left', $db), dba_fetch('right', $db));
    $keys = [];
    for ($key = dba_firstkey($db); $key !== false && count($keys) < 4; $key = dba_nextkey($db)) {
        $keys[] = $key;
    }
    echo "reopened keys: ";
    var_dump($keys === ['left', 'right', ''], $key === false);
    dba_close($db);
}

echo "large suffix:\n";
$db = dba_open($path, 'n', 'flatfile');
$large = str_repeat('x', 8193);
dba_insert('', '', $db);
dba_insert('large', $large, $db);
dba_insert('last', 'last value', $db);
var_dump(dba_delete('', $db), dba_fetch('large', $db) === $large, dba_fetch('last', $db));
dba_close($db);

echo "only key:\n";
$db = dba_open($path, 'n', 'flatfile');
dba_insert('', 'only value', $db);
var_dump(dba_delete('', $db), dba_firstkey($db), dba_replace('', '', $db), dba_fetch('', $db));
dba_close($db);

foreach (['delete', 'replace'] as $operation) {
    foreach ([
        'first' => [['', 'gone', 'left', 'right'], '', ['left', 'right']],
        'current' => [['left', '', 'right'], '', ['right']],
        'after' => [['left', '', 'right'], 'left', ['right']],
        'before' => [['left', '', 'right'], 'right', []],
    ] as $position => [$order, $current, $expected]) {
        echo "$operation during iteration, $position:\n";
        $db = dba_open($path, 'n', 'flatfile');
        foreach ($order as $key) {
            dba_insert($key, 'value', $db);
        }
        dba_delete('gone', $db);
        for ($key = dba_firstkey($db); $key !== false && $key !== $current; $key = dba_nextkey($db)) {
        }
        var_dump($key === $current);
        if ($operation === 'delete') {
            var_dump(dba_delete('', $db));
        } else {
            var_dump(dba_replace('', 'updated', $db));
            $expected[] = '';
        }
        $keys = [];
        for ($key = dba_nextkey($db); $key !== false && count($keys) < 5; $key = dba_nextkey($db)) {
            $keys[] = $key;
        }
        var_dump($keys === $expected, $key === false);
        dba_close($db);
    }
}
?>
--CLEAN--
<?php
require_once __DIR__ . '/setup/setup_dba_tests.inc';
cleanup_standard_db(__DIR__ . '/dba_flatfile_empty_key.db');
?>
--EXPECT--
first:
delete: bool(true)
deleted key: bool(false)
bool(false)
delete again: bool(false)
other records: string(4) "LEFT"
string(5) "RIGHT"
remaining keys: bool(true)
bool(true)
reinsert: bool(true)
bool(true)
replace: bool(true)
reopened: string(7) "updated"
string(4) "LEFT"
string(5) "RIGHT"
reopened keys: bool(true)
bool(true)
middle:
delete: bool(true)
deleted key: bool(false)
bool(false)
delete again: bool(false)
other records: string(4) "LEFT"
string(5) "RIGHT"
remaining keys: bool(true)
bool(true)
reinsert: bool(true)
bool(true)
replace: bool(true)
reopened: string(7) "updated"
string(4) "LEFT"
string(5) "RIGHT"
reopened keys: bool(true)
bool(true)
last:
delete: bool(true)
deleted key: bool(false)
bool(false)
delete again: bool(false)
other records: string(4) "LEFT"
string(5) "RIGHT"
remaining keys: bool(true)
bool(true)
reinsert: bool(true)
bool(true)
replace: bool(true)
reopened: string(7) "updated"
string(4) "LEFT"
string(5) "RIGHT"
reopened keys: bool(true)
bool(true)
large suffix:
bool(true)
bool(true)
string(10) "last value"
only key:
bool(true)
bool(false)
bool(true)
string(0) ""
delete during iteration, first:
bool(true)
bool(true)
bool(true)
bool(true)
delete during iteration, current:
bool(true)
bool(true)
bool(true)
bool(true)
delete during iteration, after:
bool(true)
bool(true)
bool(true)
bool(true)
delete during iteration, before:
bool(true)
bool(true)
bool(true)
bool(true)
replace during iteration, first:
bool(true)
bool(true)
bool(true)
bool(true)
replace during iteration, current:
bool(true)
bool(true)
bool(true)
bool(true)
replace during iteration, after:
bool(true)
bool(true)
bool(true)
bool(true)
replace during iteration, before:
bool(true)
bool(true)
bool(true)
bool(true)
