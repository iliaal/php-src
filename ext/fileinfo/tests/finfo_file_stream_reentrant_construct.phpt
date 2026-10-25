--TEST--
finfo_file() stream wrapper re-entering finfo::__construct()
--EXTENSIONS--
fileinfo
--FILE--
<?php

final class ReentrantFinfoStream
{
    public $context;
    public static finfo $finfo;
    public static string $position = '';

    public function stream_open(string $path, string $mode, int $options, ?string &$openedPath): bool
    {
        self::$finfo->__construct(FILEINFO_NONE);
        return true;
    }

    public function stream_read(int $count): string
    {
        return '';
    }

    public function stream_stat(): array
    {
        return ['size' => 0];
    }

    public function stream_eof(): bool
    {
        return true;
    }

    public function stream_cast(int $castAs)
    {
        return null;
    }

    public function stream_seek(int $offset, int $whence): bool
    {
        self::$position = "$offset:$whence";
        return true;
    }

    public function stream_tell(): int
    {
        return 0;
    }
}

$finfo = new finfo(FILEINFO_MIME_TYPE);
ReentrantFinfoStream::$finfo = $finfo;
stream_wrapper_register('finfo-reentry', ReentrantFinfoStream::class);

var_dump(finfo_file($finfo, 'finfo-reentry://empty'));
var_dump($finfo->file(__FILE__));

?>
--EXPECT--
string(19) "application/x-empty"
string(22) "PHP script, ASCII text"
