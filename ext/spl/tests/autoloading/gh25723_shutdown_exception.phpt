--TEST--
GH-25723 (exception thrown from autoloader destructor during zend_autoload_shutdown())
--FILE--
<?php

class ShutdownThrowingLoader {
    public function autoload(string $class): void {}
    public function __destruct() {
        spl_autoload_register([new self(), 'autoload']);
        throw new RuntimeException('shutdown dtor boom');
    }
}

ob_start(function (string $buf): string {
    spl_autoload_register([new ShutdownThrowingLoader(), 'autoload']);
    return $buf;
});
echo "ready\n";
?>
--EXPECTF--
ready

Fatal error: Uncaught RuntimeException: shutdown dtor boom in %s:%d
Stack trace:
#0 [internal function]: ShutdownThrowingLoader->__destruct()
#1 {main}
  thrown in %s on line %d
