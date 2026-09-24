--TEST--
Fully occupied CDF directory names are NUL terminated
--EXTENSIONS--
fileinfo
--FILE--
<?php
$filename = __DIR__ . '/bug79756-cdf-name-oob.xls';
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $filename);
echo $mime, "\n";
finfo_close($finfo);
?>
--EXPECT--
application/vnd.ms-office
