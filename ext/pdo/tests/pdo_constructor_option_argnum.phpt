--TEST--
PDO constructor option attribute errors report $options as argument #4
--EXTENSIONS--
pdo
pdo_sqlite
--FILE--
<?php
try {
    new PDO('sqlite::memory:', null, null, [PDO::ATTR_ERRMODE => 999]);
} catch (Throwable $e) {
    echo get_class($e), ": ", str_replace(PHP_VERSION, '<ver>', $e->getMessage()), "\n";
}
?>
--EXPECT--
ValueError: PDO::__construct(): Argument #4 ($options) Error mode must be one of the PDO::ERRMODE_* constants
