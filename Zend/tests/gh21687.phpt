--TEST--
GH-21687 (Assertion failure in zend_enum_fetch_case_name after array_walk on enum)
--FILE--
<?php
enum Foo: int {
    case Bar = 0;
}

$bar = Foo::Bar;
array_walk($bar, function($v) {});
var_dump($bar);
echo $bar->name . "\n";
echo $bar->value . "\n";
?>
--EXPECT--
enum(Foo::Bar)
Bar
0
