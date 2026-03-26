--TEST--
GH-17399 (iconv_substr() leak on bailout)
--EXTENSIONS--
iconv
--INI--
memory_limit=2M
--FILE--
<?php
$str = str_repeat('a', 1024 * 1024);
iconv_substr($str, 0, 1024 * 1024, 'UTF-8');
?>
--EXPECTF--
Fatal error: Allowed memory size of %d bytes exhausted %s in %s on line %d
