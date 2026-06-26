--TEST--
JIT FETCH_DIM_RW: 004
--INI--
opcache.enable=1
opcache.enable_cli=1
opcache.file_update_protection=0
--FILE--
<?php
set_error_handler(function(y$y) {
});
$k=[];
$y[$k]++;
?>
--EXPECTF--
Fatal error: Uncaught TypeError: Cannot access offset of type array on array in %sfetch_dim_rw_004.php:%d
Stack trace:
#0 {main}
  thrown in %sfetch_dim_rw_004.php on line %d
