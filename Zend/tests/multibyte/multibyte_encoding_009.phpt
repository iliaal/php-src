--TEST--
Zend Multibyte and a shebang line at the end of a converted script
--EXTENSIONS--
mbstring
--INI--
zend.multibyte=1
zend.script_encoding=ISO-8859-1
internal_encoding=UTF-8
--FILE--
<?php
$file = __DIR__ . '/multibyte_encoding_009.inc';
file_put_contents($file, '#!/usr/bin/env php');
include $file;
echo "Done\n";
?>
--CLEAN--
<?php
@unlink(__DIR__ . '/multibyte_encoding_009.inc');
?>
--EXPECT--
Done
