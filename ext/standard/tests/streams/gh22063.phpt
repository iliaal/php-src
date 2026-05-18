--TEST--
GH-22063 (Stream filter chain UAF via self-removal during callback)
--FILE--
<?php
class SelfRemovingFilter extends php_user_filter {
    public $stream;
    public static ?string $key = null;
    public static bool $on_closing_only = false;

    public function filter($in, $out, &$consumed, $closing): int
    {
        while ($bucket = stream_bucket_make_writeable($in)) {
            $consumed += $bucket->datalen;
            stream_bucket_append($out, $bucket);
        }
        if (self::$key !== null && (!self::$on_closing_only || $closing)) {
            $res = $GLOBALS[self::$key];
            self::$key = null;
            stream_filter_remove($res);
        }
        return PSFS_PASS_ON;
    }
}

stream_filter_register('self-removing', SelfRemovingFilter::class);

echo "write side: ";
$f = fopen('php://memory', 'r+');
SelfRemovingFilter::$key = 'write_res';
SelfRemovingFilter::$on_closing_only = false;
$GLOBALS['write_res'] = stream_filter_append($f, 'self-removing', STREAM_FILTER_WRITE);
fwrite($f, 'hello');
fwrite($f, ' world');
rewind($f);
echo stream_get_contents($f), "\n";

echo "read side: ";
$f = fopen('php://memory', 'r+');
fwrite($f, 'abcdefghij');
rewind($f);
SelfRemovingFilter::$key = 'read_res';
SelfRemovingFilter::$on_closing_only = false;
$GLOBALS['read_res'] = stream_filter_append($f, 'self-removing', STREAM_FILTER_READ);
echo fread($f, 4), '|', fread($f, 6), "\n";

echo "closing-flush side: ";
$f = fopen('php://memory', 'r+');
SelfRemovingFilter::$key = 'close_res';
SelfRemovingFilter::$on_closing_only = true;
$GLOBALS['close_res'] = stream_filter_append($f, 'self-removing', STREAM_FILTER_WRITE);
stream_filter_remove($GLOBALS['close_res']);
echo "ok\n";
?>
--EXPECT--
write side: hello world
read side: abcd|efghij
closing-flush side: ok
