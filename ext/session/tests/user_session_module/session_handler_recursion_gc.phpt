--TEST--
session_gc() calls rejected recursively from SessionHandler::write() remain rejected
--INI--
session.save_path=
session.name=PHPSESSID
--EXTENSIONS--
session
--FILE--
<?php
class Handler extends SessionHandler
{
    public int $gcCalls = 0;

    public function open(string $path, string $name): bool
    {
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function read(string $id): string|false
    {
        return '';
    }

    public function write(string $id, string $data): bool
    {
        var_dump(session_gc());
        var_dump(session_gc());
        return true;
    }

    public function destroy(string $id): bool
    {
        return true;
    }

    public function gc(int $maxLifetime): int|false
    {
        $this->gcCalls++;
        return 0;
    }
}

$handler = new Handler();
session_set_save_handler($handler, true);
session_start();
$_SESSION['key'] = 'value';
session_write_close();
var_dump($handler->gcCalls);
?>
--EXPECTF--
Warning: session_gc(): Cannot call session save handler in a recursive manner in %s on line %d
bool(false)

Warning: session_gc(): Cannot call session save handler in a recursive manner in %s on line %d
bool(false)
int(0)
