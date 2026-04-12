--TEST--
GH-21731: Xoshiro256StarStar::__unserialize() must reject the all-zero state
--FILE--
<?php declare(strict_types = 1);

use Random\Engine\Xoshiro256StarStar;

try {
    $engine = new Xoshiro256StarStar(42);
    $engine->__unserialize([
        [],
        ['0000000000000000', '0000000000000000', '0000000000000000', '0000000000000000'],
    ]);
    echo "FAIL: __unserialize() accepted zero state\n";
} catch (\Exception $e) {
    echo $e::class, ': ', $e->getMessage(), "\n";
}

?>
--EXPECT--
Exception: Invalid serialization data for Random\Engine\Xoshiro256StarStar object
