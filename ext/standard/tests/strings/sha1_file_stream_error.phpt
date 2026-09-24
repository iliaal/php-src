--TEST--
sha1_file() returns false when a stream read fails before EOF
--FILE--
<?php
class TestStream
{
    public $context;
    public bool $fail = false;
    private int $position = 0;

    public function stream_open(string $path): bool
    {
        $this->fail = str_ends_with($path, '/error');

        return true;
    }

    public function stream_read(int $count): string|false
    {
        if ($this->position++ === 0) {
            return 'prefix';
        }

        return $this->fail ? false : '';
    }

    public function stream_eof(): bool
    {
        return !$this->fail && $this->position > 1;
    }
}

stream_wrapper_register('test', TestStream::class);

var_dump(sha1_file('test://error'));
var_dump(sha1_file('test://eof'));
?>
--EXPECT--
bool(false)
string(40) "b4ebfe34d0fa97f0dd2bb1234fad8f59805f4e8d"
