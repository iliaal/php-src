--TEST--
Encode exception must still close the session save handler
--EXTENSIONS--
session
--INI--
session.use_cookies=0
session.cache_limiter=
session.gc_probability=0
--FILE--
<?php
class ThrowingEncodeHandler implements SessionHandlerInterface
{
    public bool $closed = false;
    public bool $written = false;

    public function open(string $path, string $name): bool
    {
        return true;
    }

    public function close(): bool
    {
        $this->closed = true;
        return true;
    }

    public function read(string $id): string|false
    {
        return '';
    }

    public function write(string $id, string $data): bool
    {
        $this->written = true;
        return true;
    }

    public function destroy(string $id): bool
    {
        return true;
    }

    public function gc(int $max_lifetime): int|false
    {
        return 0;
    }
}

class ThrowingSessionValue
{
    public function __serialize(): array
    {
        throw new Exception('encode exploded');
    }
}

$handler = new ThrowingEncodeHandler;
session_set_save_handler($handler, true);
session_id('encode-exception');
session_start();
$_SESSION['value'] = new ThrowingSessionValue;

try {
    session_write_close();
} catch (Throwable $e) {
    echo $e::class, ': ', $e->getMessage(), "\n";
}

var_dump($handler->closed);
var_dump($handler->written);
?>
--EXPECT--
Exception: encode exploded
bool(true)
bool(false)
