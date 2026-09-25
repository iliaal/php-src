--TEST--
BcMath\Number div() canonicalizes truncated zero
--EXTENSIONS--
bcmath
--INI--
bcmath.scale=0
--FILE--
<?php
$cases = [
    ['-0.001', '1', 0],
    ['-0.001', '10', 2],
];

foreach ($cases as [$dividend, $divisor, $scale]) {
    $quotient = (new BcMath\Number($dividend))->div($divisor, $scale);
    var_dump((string) $quotient, $quotient->compare('0'), $quotient == new BcMath\Number('0'));
}
?>
--EXPECT--
string(1) "0"
int(0)
bool(true)
string(4) "0.00"
int(0)
bool(true)
