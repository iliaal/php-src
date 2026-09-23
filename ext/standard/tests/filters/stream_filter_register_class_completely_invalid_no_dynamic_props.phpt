--TEST--
stream_filter_register() rejects a class that does not extend php_user_filter
--FILE--
<?php

try {
    stream_filter_register("invalid_filter", SensitiveParameter::class);
} catch (Throwable $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}
?>
--EXPECT--
ValueError: stream_filter_register(): Argument #2 ($class) must be a subclass of php_user_filter
