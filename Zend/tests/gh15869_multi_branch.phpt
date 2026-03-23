--TEST--
GH-15869 (Stack overflow in zend_array_destroy with multiple deeply nested branches)
--FILE--
<?php
ini_set('memory_limit', '1G');

/* Two independent deeply nested chains in one array.
 * Without the destroy stack, one branch would recurse via rc_dtor_func. */
$a = [];
$b = [];
for ($i = 0; $i < 200000; $i++) {
    $a = [$a];
    $b = [$b];
}
$c = [$a, $b];
unset($a, $b);
echo "Built\n";
unset($c);
echo "Freed\n";
?>
--EXPECT--
Built
Freed
