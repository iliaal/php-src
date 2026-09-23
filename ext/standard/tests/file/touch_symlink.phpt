--TEST--
touch() does not create a file through a dangling symlink
--SKIPIF--
<?php
if (!function_exists('symlink')) {
    die('skip symlink() is not available');
}
$target = tempnam(sys_get_temp_dir(), 'php-touch-symlink-');
$link = $target . '-link';
if (!$target || !@symlink($target, $link)) {
    if ($target) {
        @unlink($target);
    }
    die('skip symlink creation is unavailable');
}
@unlink($link);
@unlink($target);
?>
--FILE--
<?php
$base = __DIR__ . '/touch-symlink-' . getmypid();
$link = $base . '-link';
$target = $base . '-target';

var_dump(file_put_contents($target, 'keep'));
var_dump(@symlink($target, $link));
var_dump(touch($link, 100));
echo "target contents: ", file_get_contents($target), "\n";
var_dump(filemtime($target));
unlink($target);

var_dump(@touch($link));
var_dump(file_exists($target));

$url = 'file://' . $link;
var_dump(@touch($url));
var_dump(file_exists($target));

unlink($link);
?>
--EXPECT--
int(4)
bool(true)
bool(true)
target contents: keep
int(100)
bool(false)
bool(false)
bool(false)
bool(false)
