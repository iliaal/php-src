--TEST--
FFI callback destructor handles consumed __call handler
--EXTENSIONS--
ffi
--SKIPIF--
<?php
try {
    FFI::cdef("void qsort(void *, size_t, size_t, void *);", "libc.so.6");
} catch (Throwable $_) {
    die('skip libc.so.6 not available');
}
?>
--INI--
ffi.enable=1
--FILE--
<?php
$ffi = FFI::cdef(<<<'CDEF'
typedef int (*comparator)(const void *, const void *);
void qsort(void *, size_t, size_t, comparator);
CDEF, 'libc.so.6');

class Callback {
    public int $calls = 0;

    public function __call(string $name, array $arguments): int {
        $this->calls++;

        return 0;
    }
}

$values = $ffi->new('int[2]');
$values[0] = 3;
$values[1] = 1;
$callback = new Callback();
$comparator = $ffi->new('comparator');
$comparator = [$callback, 'compare'];
$ffi->qsort($values, 2, 4, $comparator);
var_dump($callback->calls > 0);
?>
--EXPECT--
bool(true)
