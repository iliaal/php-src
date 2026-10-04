--TEST--
getimagesize() rejects out-of-range XBM dimensions
--FILE--
<?php
var_dump(getimagesize(__DIR__ . '/xbm_out_of_range.xbm'));
?>
--EXPECT--
bool(false)
