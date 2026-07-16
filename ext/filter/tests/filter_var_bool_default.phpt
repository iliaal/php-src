--TEST--
filter_var() FILTER_VALIDATE_BOOL keeps false when options.default is set
--EXTENSIONS--
filter
--FILE--
<?php
$opts = ['options' => ['default' => 'OOPS']];
foreach (['0', 'false', 'off', 'no', ''] as $v) {
	var_dump($v, filter_var($v, FILTER_VALIDATE_BOOL, $opts));
}
var_dump(filter_var('maybe', FILTER_VALIDATE_BOOL, $opts));
var_dump(filter_var('true', FILTER_VALIDATE_BOOL, $opts));
var_dump(filter_var('false', FILTER_VALIDATE_BOOL, [
	'options' => ['default' => 'OOPS'],
	'flags' => FILTER_NULL_ON_FAILURE,
]));
var_dump(filter_var('maybe', FILTER_VALIDATE_BOOL, [
	'options' => ['default' => 'OOPS'],
	'flags' => FILTER_NULL_ON_FAILURE,
]));
?>
--EXPECT--
string(1) "0"
bool(false)
string(5) "false"
bool(false)
string(3) "off"
bool(false)
string(2) "no"
bool(false)
string(0) ""
bool(false)
string(4) "OOPS"
bool(true)
bool(false)
string(4) "OOPS"
