--TEST--
php://filter propagates invalid filter creation failure
--FILE--
<?php
var_dump(fopen('php://filter/invalid.filter/resource=php://temp', 'r'));
?>
--EXPECTF--
Warning: fopen(): Unable to locate filter "invalid.filter" in %s on line %d

Warning: fopen(): Unable to create filter (invalid.filter) in %s on line %d

Warning: fopen(php://filter/invalid.filter/resource=php://temp): Failed to open stream: operation failed in %s on line %d
bool(false)
