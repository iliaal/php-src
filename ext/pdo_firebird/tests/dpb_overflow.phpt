--TEST--
PDO_Firebird rejects values that overflow the database parameter buffer
--EXTENSIONS--
pdo_firebird
--SKIPIF--
<?php require('skipif.inc'); ?>
--FILE--
<?php
require("testdb.inc");

$tests = [
    'one-byte length overflow' => [str_repeat('u', 256), PDO_FIREBIRD_TEST_PASS],
    'DPB capacity overflow' => [str_repeat('u', 200), str_repeat('p', 52)],
];

foreach ($tests as $description => [$username, $password]) {
    try {
        new PDO(PDO_FIREBIRD_TEST_DSN, $username, $password);
        echo "$description: accepted\n";
    } catch (ValueError $e) {
        echo "$description: ", $e->getMessage(), "\n";
    }
}
?>
--EXPECT--
one-byte length overflow: Firebird database parameter value is too long
DPB capacity overflow: Firebird database parameter value is too long
