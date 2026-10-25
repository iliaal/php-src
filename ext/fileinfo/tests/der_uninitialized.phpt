--TEST--
fileinfo: zero-length DER UTCTIME data does not use an uninitialized buffer
--EXTENSIONS--
fileinfo
--FILE--
<?php
$magic = tempnam(sys_get_temp_dir(), 'der-magic-');
$input = tempnam(sys_get_temp_dir(), 'der-input-');
file_put_contents($magic, "0\tder\tutc_time=x\tDER UTCTime [%s]\n");
file_put_contents($input, "\x17\x00\x00");

$finfo = finfo_open(FILEINFO_NONE, $magic);
var_dump(finfo_buffer($finfo, file_get_contents($input)));

finfo_close($finfo);
unlink($input);
unlink($magic);
?>
--EXPECT--
string(14) "DER UTCTime []"
