--TEST--
zip_entry_read() keeps the parent archive alive after zip_close()
--EXTENSIONS--
zip
--FILE--
<?php
error_reporting(E_ALL & ~E_DEPRECATED);

$file = tempnam(sys_get_temp_dir(), 'zip-entry-parent-');
$archive = new ZipArchive();
$archive->open($file, ZipArchive::CREATE | ZipArchive::OVERWRITE);
$archive->addFromString('entry.txt', 'contents');
$archive->close();

$zip = zip_open($file);
$entry = zip_read($zip);
zip_close($zip);

var_dump(zip_entry_read($entry, 8));
var_dump(zip_entry_name($entry));
zip_entry_close($entry);
unlink($file);
?>
--EXPECT--
string(8) "contents"
string(9) "entry.txt"
