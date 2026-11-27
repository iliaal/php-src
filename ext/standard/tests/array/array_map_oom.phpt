--TEST--
array_map() with null callback finishes packed hash fill before returning on OOM
--SKIPIF--
<?php
if (getenv('USE_ZEND_ALLOC') === '0') die('skip requires ZendMM');
?>
--INI--
memory_limit=8M
--FILE--
<?php
$base = memory_get_usage();
register_shutdown_function(function () use ($base) {
    var_dump(memory_get_usage() - $base < 200000);
});
function trigger(): void {
    $a = range(1, 25000);
    array_map(null, $a, $a, $a);
}
trigger();
?>
--EXPECTF--
Fatal error: Allowed memory size of 8388608 bytes exhausted %S(tried to allocate %d bytes) in %s on line %d
bool(true)
