--TEST--
IntlPartsIterator must not retain the current element after destruction
--SKIPIF--
<?php if (!extension_loaded('intl')) die('skip intl extension not available'); ?>
--INI--
memory_limit=64M
--FILE--
<?php
$bi = IntlBreakIterator::createWordInstance('en');
$bi->setText('hello world foo bar baz');
$m0 = memory_get_usage();
for ($i = 0; $i < 300000; $i++) {
    foreach ($bi->getPartsIterator() as $v) {
        break;
    }
}
$m1 = memory_get_usage();
var_dump($m1 - $m0 < 4 * 1024 * 1024);
?>

--EXPECT--
bool(true)

