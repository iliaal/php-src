--TEST--
The save handler recursion guard survives a rejected nested call
--INI--
session.save_handler=files
session.name=PHPSESSID
session.gc_probability=0
--EXTENSIONS--
session
--FILE--
<?php

ob_start();

class MySessionHandler extends SessionHandler
{
    public bool $armed = true;
    public int $gcEntered = 0;

    public function gc(int $max_lifetime): int|false
    {
        $this->gcEntered++;
        return 0;
    }

    public function write(string $id, string $data): bool
    {
        if ($this->armed) {
            $this->armed = false;
            var_dump(session_gc());
            var_dump(session_gc());
        }
        return parent::write($id, $data);
    }
}

$handler = new MySessionHandler();
session_set_save_handler($handler, true);
session_start();
$_SESSION['key'] = 'value';
session_write_close();

echo 'gc() entered ', $handler->gcEntered, " time(s)\n";

?>
--EXPECTF--
Warning: session_gc(): Cannot call session save handler in a recursive manner in %s on line %d
bool(false)

Warning: session_gc(): Cannot call session save handler in a recursive manner in %s on line %d
bool(false)
gc() entered 0 time(s)
