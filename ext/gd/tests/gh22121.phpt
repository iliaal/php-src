--TEST--
GH-22121 (Double free in gdImageSetStyle() after overflow-triggered early return)
--EXTENSIONS--
gd
--INI--
memory_limit=-1
--SKIPIF--
<?php
if (!getenv('RUN_RESOURCE_HEAVY_TESTS')) die('skip resource-heavy test');
if (PHP_INT_SIZE < 8) die('skip 64-bit only (allocates ~10 GiB)');
?>
--FILE--
<?php
$im = imagecreatetruecolor(1, 1);
imagesetstyle($im, [0]);
imagesetstyle($im, array_fill(0, 536870912, 0));
unset($im);
echo "no double free\n";
?>
--EXPECTF--
Warning: imagesetstyle(): Product of memory allocation multiplication would exceed INT_MAX, failing operation gracefully
 in %s on line %d
no double free
