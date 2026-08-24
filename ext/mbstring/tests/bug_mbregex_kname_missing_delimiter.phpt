--TEST--
mb_ereg_replace() \k<name> with missing delimiter must not embed NUL in output
--FILE--
<?php
mb_regex_encoding('UTF-8');
$r = mb_ereg_replace('a', '\k<name', 'a');
var_dump(bin2hex($r));
var_dump($r === '\k<name');
$r = mb_ereg_replace('(a)', '\k<', 'a');
var_dump(bin2hex($r));
var_dump($r === '\k<');
?>
--EXPECT--
string(14) "5c6b3c6e616d65"
bool(true)
string(6) "5c6b3c"
bool(true)
