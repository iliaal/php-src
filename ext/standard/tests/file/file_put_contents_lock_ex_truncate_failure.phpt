--TEST--
file_put_contents() LOCK_EX returns false when the stream cannot be truncated
--FILE--
<?php
class FailingTruncateWrapper
{
    public $context;

    public function stream_open($path, $mode, $options, &$openedPath)
    {
        return true;
    }

    public function stream_lock($operation)
    {
        return true;
    }

    public function stream_truncate($newSize)
    {
        echo "truncate: $newSize\n";
        return false;
    }

    public function stream_write($data)
    {
        echo "write\n";
        return strlen($data);
    }
}

var_dump(stream_wrapper_unregister('file'));
var_dump(stream_wrapper_register('file', FailingTruncateWrapper::class));
var_dump(file_put_contents('file:///test', 'data', LOCK_EX));
?>
--EXPECT--
bool(true)
bool(true)
truncate: 0
bool(false)
