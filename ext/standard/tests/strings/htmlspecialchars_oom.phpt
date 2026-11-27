--TEST--
htmlspecialchars() stops cleanly on soft memory limit during ASCII run realloc
--SKIPIF--
<?php
if (getenv('USE_ZEND_ALLOC') === '0') die('skip requires ZendMM');
?>
--INI--
memory_limit=20M
--FILE--
<?php
register_shutdown_function(function () {
    echo "SHUTDOWN_OK
";
});
$s = str_repeat(str_repeat('"', 9) . 'a', 580000);
htmlspecialchars($s);
?>
--EXPECTF--
Fatal error: Allowed memory size of 20971520 bytes exhausted %S(tried to allocate %d bytes) in %s on line %d
SHUTDOWN_OK
