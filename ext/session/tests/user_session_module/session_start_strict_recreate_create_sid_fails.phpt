--TEST--
session_start() strict mode re-creation must abort when create_sid fails
--INI--
session.use_trans_sid=1
session.use_only_cookies=0
error_reporting=E_ALL & ~E_DEPRECATED
--EXTENSIONS--
session
--FILE--
<?php

class FailingHandler extends SessionHandler
{
    public function create_sid(): string
    {
        throw new RuntimeException('create_sid failed');
    }

    public function validateId(string $id): bool
    {
        return false;
    }
}

session_set_save_handler(new FailingHandler(), true);
session_id(str_repeat('a', 32));

try {
    var_dump(session_start(['use_strict_mode' => true]));
} catch (Throwable $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

var_dump(session_status() === PHP_SESSION_ACTIVE);
var_dump(defined('SID'));

?>
--EXPECT--
Error: Session id must be a string
bool(false)
bool(false)
