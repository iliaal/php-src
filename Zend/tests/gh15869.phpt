--TEST--
GH-15869 (Stack overflow in zend_array_destroy when freeing deeply nested arrays)
--FILE--
<?php
ini_set('memory_limit', '512M');

$a = [];
for ($i = 0; $i < 200000; $i++) {
    $a = [$a];
}
echo "Built\n";
unset($a);
echo "Freed\n";
?>
--EXPECT--
Built
Freed
