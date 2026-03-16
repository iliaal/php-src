--TEST--
GH-20042 (SEGV in array.c when error handler clobbers IAP object argument)
--FILE--
<?php
foreach (['prev', 'next', 'end', 'reset', 'current', 'key'] as $func) {
    $obj = new stdClass;
    set_error_handler(function () use (&$obj) {
        $obj = 0;
    });
    var_dump($func($obj));
    restore_error_handler();
}
?>
--EXPECT--
bool(false)
bool(false)
bool(false)
bool(false)
bool(false)
NULL
