--TEST--
Encode failure must still close the session save handler
--EXTENSIONS--
session
--INI--
session.use_cookies=0
session.cache_limiter=
session.gc_probability=0
--FILE--
<?php
class FailingEncodeHandler implements SessionHandlerInterface
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

$handler = new FailingEncodeHandler;
session_set_save_handler($handler, true);
session_id('encode-failure');
session_start();
$_SESSION['bad|key'] = 'v';
session_write_close();

var_dump($handler->closed);
var_dump($handler->written);
?>
--EXPECTF--
Warning: session_write_close(): Failed to write session data. Data contains invalid key "bad|key" in %s on line %d
bool(true)
bool(false)
