--TEST--
SessionHandler::close() that is never delegated still releases the default handler
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
    public function close(): bool
    {
        return true;
    }
}

session_set_save_handler(new MySessionHandler(), true);
session_start();
$_SESSION['key'] = 'value';
var_dump(session_write_close());
var_dump(session_status() === PHP_SESSION_NONE);

?>
--EXPECT--
bool(true)
bool(true)
