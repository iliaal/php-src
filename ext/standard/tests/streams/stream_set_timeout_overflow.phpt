--TEST--
stream_set_timeout() rejects overflow while preserving signed timeout components
--SKIPIF--
<?php
if (PHP_INT_SIZE != 8) {
    die("skip overflowing a 64-bit timeval requires 64-bit PHP integers");
}
?>
--FILE--
<?php
class TimeoutStream
{
    public $context;
    public array $timeout = [];

    public function stream_open(string $path, string $mode, int $options, ?string &$openedPath): bool
    {
        return true;
    }

    public function stream_eof(): bool
    {
        return false;
    }

    public function stream_set_option(int $option, int $seconds, int $microseconds): bool
    {
        $this->timeout = [$seconds, $microseconds];
        return true;
    }
}

stream_wrapper_register('timeout', TimeoutStream::class);
$stream = fopen('timeout://test', 'r');
$wrapper = stream_get_meta_data($stream)['wrapper_data'];
$maximum = PHP_OS_FAMILY === 'Windows' ? 2147483647 : PHP_INT_MAX;
$minimum = -$maximum - 1;

echo "unrepresentable sums:\n";
foreach ([[PHP_INT_MAX, 1_000_000], [PHP_INT_MIN, -1_000_000],
          [PHP_INT_MAX, PHP_INT_MAX], [PHP_INT_MIN, PHP_INT_MIN],
          [$maximum, 1_000_000], [$minimum, -1_000_000]] as [$seconds, $microseconds]) {
    $wrapper->timeout = [];
    try {
        stream_set_timeout($stream, $seconds, $microseconds);
        echo "unexpected success\n";
    } catch (ValueError $e) {
        echo $e::class, ': ', $e->getMessage(), "\n";
    }
    var_dump($wrapper->timeout === []);
}

echo "signed normalization and limits:\n";
foreach ([[-1, 0, -1, 0], [0, -1, 0, -1], [-2, 1_000_000, -1, 0],
          [1, -2_000_000, -1, 0], [10, 2_345_678, 12, 345678],
          [10, -2_345_678, 8, -345678],
          [$maximum - 1, 1_000_000, $maximum, 0],
          [$minimum + 1, -1_000_000, $minimum, 0],
          [$maximum, 999999, $maximum, 999999],
          [$minimum, -999999, $minimum, -999999]] as [$seconds, $microseconds, $expectedSeconds, $expectedMicroseconds]) {
    var_dump(stream_set_timeout($stream, $seconds, $microseconds)
        && $wrapper->timeout === [$expectedSeconds, $expectedMicroseconds]);
}
?>
--EXPECT--
unrepresentable sums:
ValueError: stream_set_timeout(): Argument #2 ($seconds) is out of range when combined with argument #3 ($microseconds)
bool(true)
ValueError: stream_set_timeout(): Argument #2 ($seconds) is out of range when combined with argument #3 ($microseconds)
bool(true)
ValueError: stream_set_timeout(): Argument #2 ($seconds) is out of range when combined with argument #3 ($microseconds)
bool(true)
ValueError: stream_set_timeout(): Argument #2 ($seconds) is out of range when combined with argument #3 ($microseconds)
bool(true)
ValueError: stream_set_timeout(): Argument #2 ($seconds) is out of range when combined with argument #3 ($microseconds)
bool(true)
ValueError: stream_set_timeout(): Argument #2 ($seconds) is out of range when combined with argument #3 ($microseconds)
bool(true)
signed normalization and limits:
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
