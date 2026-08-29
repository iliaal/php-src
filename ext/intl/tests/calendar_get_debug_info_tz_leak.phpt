--TEST--
IntlCalendar get_debug_info() must not leak the time zone wrapper object
--EXTENSIONS--
intl
--FILE--
<?php
$cal = IntlCalendar::createInstance('UTC');
$m0 = memory_get_usage();
for ($i = 0; $i < 1000; $i++) {
	ob_start();
	var_dump($cal);
	ob_end_clean();
}
$m1 = memory_get_usage();
var_dump($m1 - $m0 < 10000);
?>
--EXPECT--
bool(true)
