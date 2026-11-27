--TEST--
str_split() finishes packed hash fill before returning on OOM
--SKIPIF--
<?php
if (getenv('USE_ZEND_ALLOC') === '0') die('skip requires ZendMM');
?>
--INI--
memory_limit=6M
--FILE--
<?php
$base = memory_get_usage();
register_shutdown_function(function () use ($base) {
    var_dump(memory_get_usage() - $base < 200000);
});
function trigger(): void {
    $s = str_repeat('a', 1000000);
    str_split($s, 16);
}
trigger();
?>
--EXPECTF--
Fatal error: Allowed memory size of 6291456 bytes exhausted %S(tried to allocate %d bytes) in %s on line %d
bool(true)
