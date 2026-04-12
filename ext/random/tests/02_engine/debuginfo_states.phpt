--TEST--
GH-21730: __debugInfo() exposes the engine state under __states
--FILE--
<?php declare(strict_types = 1);

use Random\Engine\Mt19937;
use Random\Engine\PcgOneseq128XslRr64;
use Random\Engine\Xoshiro256StarStar;

$mt = new Mt19937(1234);
$mtInfo = $mt->__debugInfo();
var_dump(array_key_exists('__states', $mtInfo));
var_dump(count($mtInfo['__states']));

$pcg = new PcgOneseq128XslRr64(1234);
$pcgInfo = $pcg->__debugInfo();
var_dump(array_key_exists('__states', $pcgInfo));
var_dump(count($pcgInfo['__states']));

$xo = new Xoshiro256StarStar(1234);
$xoInfo = $xo->__debugInfo();
var_dump(array_key_exists('__states', $xoInfo));
var_dump(count($xoInfo['__states']));

// The serialized form and __debugInfo both round-trip through the engine's
// serialize callback, so they must produce identical state data.
var_dump($mt->__serialize()[1] === $mtInfo['__states']);
var_dump($pcg->__serialize()[1] === $pcgInfo['__states']);
var_dump($xo->__serialize()[1] === $xoInfo['__states']);

?>
--EXPECT--
bool(true)
int(626)
bool(true)
int(2)
bool(true)
int(4)
bool(true)
bool(true)
bool(true)
