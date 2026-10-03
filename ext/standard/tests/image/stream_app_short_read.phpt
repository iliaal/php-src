--TEST--
Image APP metadata handles short reads from user stream wrappers
--FILE--
<?php
class ShortReadStream
{
    public $context;
    public static string $data = '';
    public static int $position = 0;
    public static int $limit = 3;
    public static int $truncateAfter = PHP_INT_MAX;

    public function stream_open(string $path, string $mode): bool
    {
        self::$position = 0;
        return true;
    }

    public function stream_read(int $count): string
    {
        $count = min($count, self::$limit);
        if (self::$position >= self::$truncateAfter && $count > 1) {
            $count--;
        }
        $data = substr(self::$data, self::$position, $count);
        self::$position += strlen($data);
        return $data;
    }

    public function stream_eof(): bool
    {
        return self::$position >= strlen(self::$data);
    }

    public function stream_seek(int $offset, int $whence = SEEK_SET): bool
    {
        $target = match ($whence) {
            SEEK_SET => $offset,
            SEEK_CUR => self::$position + $offset,
            SEEK_END => strlen(self::$data) + $offset,
        };
        if ($target < 0) {
            return false;
        }
        self::$position = $target;
        return true;
    }

    public function stream_tell(): int
    {
        return self::$position;
    }

    public function stream_stat()
    {
        return [];
    }
}

stream_wrapper_register('short', ShortReadStream::class);

$payload = str_repeat('ABCDEFGH', 4);
$sof = "\xff\xc0" . pack('n', 11) . "\x08" . pack('n', 1) . pack('n', 1) . "\x01\x11\x00";
$jpeg = "\xff\xd8\xff\xe1" . pack('n', strlen($payload) + 2) . $payload . $sof . "\xff\xd9";

ShortReadStream::$data = $jpeg;
$info = [];
var_dump(getimagesize('short://jpeg', $info));
var_dump($info['APP1'] === $payload);

ShortReadStream::$truncateAfter = 6;
ShortReadStream::$data = substr($jpeg, 0, 12);
$info = [];
var_dump(@getimagesize('short://jpeg', $info));
var_dump(isset($info['APP1']));
?>
--EXPECT--
array(7) {
  [0]=>
  int(1)
  [1]=>
  int(1)
  [2]=>
  int(2)
  [3]=>
  string(20) "width="1" height="1""
  ["bits"]=>
  int(8)
  ["channels"]=>
  int(1)
  ["mime"]=>
  string(10) "image/jpeg"
}
bool(true)
bool(false)
bool(false)
