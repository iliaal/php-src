--TEST--
imagecreatefromxbm() rejects out-of-range XBM dimensions
--EXTENSIONS--
gd
--SKIPIF--
<?php
if (!GD_BUNDLED) {
    die("skip dimension range checks require bundled GD");
}
?>
--FILE--
<?php
var_dump(imagecreatefromxbm(__DIR__ . '/xbm_out_of_range.xbm'));
?>
--EXPECTF--
Warning: imagecreatefromxbm(): "%s" is not a valid XBM file in %s on line %d
bool(false)
