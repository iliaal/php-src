--TEST--
GlobIterator::count() on uninitialized object throws Error instead of fatal error
--FILE--
<?php
$rc = new ReflectionClass(GlobIterator::class);
$obj = $rc->newInstanceWithoutConstructor();
try {
    count($obj);
    echo "no exception\n";
} catch (Error $e) {
    echo get_class($e), ": ", $e->getMessage(), "\n";
}
?>
--EXPECT--
Error: GlobIterator is not initialized
