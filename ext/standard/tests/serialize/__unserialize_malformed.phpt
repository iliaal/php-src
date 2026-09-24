--TEST--
__unserialize() is not called for an object with a missing closing brace
--FILE--
<?php

class Test {
    public function __unserialize(array $data): void {
        echo "__unserialize called\n";
    }
}

var_dump(unserialize('O:4:"Test":0:{'));
var_dump(unserialize('O:4:"Test":0:{}'));

?>
--EXPECTF--
Warning: unserialize(): Error at offset 14 of 14 bytes in %s on line %d
bool(false)
__unserialize called
object(Test)#1 (0) {
}
