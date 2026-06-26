--TEST--
Throwing undef var in verify return
--FILE--
<?php

set_error_handler(function(int $severity, string $message, string $filename, int $lineNumber): void {
    throw new ErrorException($message, 0, $severity, $filename, $lineNumber);
});

function test(): string {
    return $test;
}

test();

?>
--EXPECTF--
Fatal error: Uncaught TypeError: test(): Return value must be of type string, null returned in %s:%d
Stack trace:
#0 %s(%d): test()
#1 {main}
  thrown in %s on line %d
