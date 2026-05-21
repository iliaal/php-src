--TEST--
GH-22112 (Assertion failure when error handler throws during NaN to bool/string coercion at function entry)
--FILE--
<?php

set_error_handler(function ($errno, $errstr) {
    throw new RuntimeException($errstr);
});

function take_bool(bool $v) {
    echo "take_bool entered\n";
}

function take_string(string $v) {
    echo "take_string entered\n";
}

$nan = fdiv(0, 0);

try {
    take_bool($nan);
} catch (RuntimeException $e) {
    echo "bool: ", $e->getMessage(), "\n";
}

try {
    take_string($nan);
} catch (RuntimeException $e) {
    echo "string: ", $e->getMessage(), "\n";
}

?>
--EXPECT--
bool: unexpected NAN value was coerced to bool
string: unexpected NAN value was coerced to string
