--TEST--
PFA: constant-expression default for a skipped optional parameter
--FILE--
<?php

const K = 7;

function f($a, $b = K, $c = 0) {
    return [$a, $b, $c];
}

class C {
    const X = 42;
    static function m($a, $b = self::X, $c = 0) {
        return [$a, $b, $c];
    }
}

enum E {
    case A;
    case B;
}

function g($a, $b = E::A, $c = 0) {
    return [$a, $b->name, $c];
}

$p = f(a: 10, c: ?);
var_dump($p(99));

$q = C::m(a: 1, c: ?);
var_dump($q(9));

$r = g(a: 1, c: ?);
var_dump($r(9));

?>
--EXPECT--
array(3) {
  [0]=>
  int(10)
  [1]=>
  int(7)
  [2]=>
  int(99)
}
array(3) {
  [0]=>
  int(1)
  [1]=>
  int(42)
  [2]=>
  int(9)
}
array(3) {
  [0]=>
  int(1)
  [1]=>
  string(1) "A"
  [2]=>
  int(9)
}
