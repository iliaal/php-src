--TEST--
ZipArchive mutators reject reentry while closing
--EXTENSIONS--
zip
--SKIPIF--
<?php
if (!method_exists(ZipArchive::class, 'registerProgressCallback')) {
    die('skip progress callbacks are not supported');
}
?>
--FILE--
<?php
$filename = __DIR__ . '/zip_close_mutator_reentry.zip';
$zip = new ZipArchive();
echo 'open: ';
var_dump($zip->open($filename, ZipArchive::CREATE | ZipArchive::OVERWRITE));
echo 'ordinary mutation: ';
var_dump($zip->addFromString('ordinary.txt', 'ordinary mutation'));

$callbackCalled = false;
$callbackError = null;
$zip->registerProgressCallback(0.0, function () use ($zip, &$callbackCalled, &$callbackError): void {
    if ($callbackCalled) {
        return;
    }

    $callbackCalled = true;
    try {
        $zip->addEmptyDir('reentrant');
    } catch (Error $e) {
        $callbackError = $e::class . ': ' . $e->getMessage();
    }
});

echo 'close: ';
var_dump($zip->close());
echo 'callback error: ', $callbackError, PHP_EOL;
echo 'callback called: ';
var_dump($callbackCalled);
echo 'reopen: ';
var_dump($zip->open($filename));
echo 'entries: ';
var_dump($zip->numFiles);
echo 'ordinary entry: ';
var_dump($zip->locateName('ordinary.txt') === 0);
echo 'reentrant entry: ';
var_dump($zip->locateName('reentrant/') === false);
echo 'final close: ';
var_dump($zip->close());
?>
--CLEAN--
<?php
@unlink(__DIR__ . '/zip_close_mutator_reentry.zip');
?>
--EXPECT--
open: bool(true)
ordinary mutation: bool(true)
close: bool(true)
callback error: Error: Already being closed
callback called: bool(true)
reopen: bool(true)
entries: int(1)
ordinary entry: bool(true)
reentrant entry: bool(true)
final close: bool(true)
