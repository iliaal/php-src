--TEST--
getimagesize() SWC path must not amplify decompression without bound
--EXTENSIONS--
zlib
--SKIPIF--
<?php
if (!defined("IMAGETYPE_SWC") && !defined("IMAGETYPE_SWF")) {
    /* IMAGETYPE_SWC may be defined when zlib static */
}
if (!function_exists('gzcompress')) die('skip zlib required');
?>
--FILE--
<?php
// Pre-fix: failed small uncompress retries with size = input * 2^factor up to 2^15.
// Post-fix: retries are capped so a tiny CWS cannot request multi-GB buffers.
$f = __DIR__ . '/swc_inflate_bomb.swc';
// Under a tight memory limit, pre-fix can OOM; post-fix returns false cleanly.
ini_set('memory_limit', '16M');
$r = @getimagesize($f);
var_dump($r);
echo "survived\n";
?>
--EXPECT--
bool(false)
survived
