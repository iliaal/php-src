--TEST--
GH-25723 (zend_autoload_shutdown() reentrancy and bounded self-re-registration)
--FILE--
<?php

class ShutdownLoopLoader {
    public static int $dtors = 0;
    public function autoload(string $class): void {}
    public function __destruct() {
        self::$dtors++;
        echo "shutdown dtor #", self::$dtors, "\n";
        echo "shutdown count: ", count(spl_autoload_functions()), "\n";
        echo "shutdown unregister: ";
        var_dump(spl_autoload_unregister([$this, 'autoload']));
        spl_autoload_register([new self(), 'autoload']);
    }
}

ob_start(function (string $buf): string {
    spl_autoload_register([new ShutdownLoopLoader(), 'autoload']);
    return $buf;
});
echo "ready\n";
?>
--EXPECT--
ready
shutdown dtor #1
shutdown count: 0
shutdown unregister: bool(false)
