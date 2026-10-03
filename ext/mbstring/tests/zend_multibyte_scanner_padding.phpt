--TEST--
Zend scanner preserves filtered buffer padding
--EXTENSIONS--
mbstring
--INI--
zend.multibyte=1
zend.script_encoding=ISO-2022-JP
internal_encoding=UTF-8
--FILE--
<?php
var_dump(eval("declare(encoding='ISO-2022-JP'); return 1;"));
?>
--EXPECT--
int(1)
