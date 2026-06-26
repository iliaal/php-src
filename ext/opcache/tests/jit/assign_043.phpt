--TEST--
JIT ASSIGN: Undef var notice promoted to exception
--INI--
opcache.enable=1
opcache.enable_cli=1
opcache.file_update_protection=0
opcache.protect_memory=1
--FILE--
<?php
set_error_handler(function($_, $m) {
    throw new Exception($m);
});
try {
    $a = $b;
    NOT_REACHED;
} catch (Exception $e) {
    echo $e->getMessage(), "\n";
}
?>
--EXPECTF--
Fatal error: Uncaught Error: Undefined constant "NOT_REACHED" in %s:%d
Stack trace:
#0 {main}
  thrown in %s on line %d
