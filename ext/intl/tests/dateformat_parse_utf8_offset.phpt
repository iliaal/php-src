--TEST--
IntlDateFormatter parse offsets use UTF-8 byte positions
--EXTENSIONS--
intl
--FILE--
<?php
$formatter = new IntlDateFormatter(
    'en_US',
    IntlDateFormatter::NONE,
    IntlDateFormatter::NONE,
    'UTC',
    IntlDateFormatter::GREGORIAN,
    'yyyy-MM-dd'
);
$prefix = "\u{1F600}";
$text = $prefix . '2017-10-12';

$position = strlen($prefix);
var_dump($formatter->parse($text, $position));
var_dump($position);

$position = strlen($prefix);
var_dump($formatter->localtime($text, $position));
var_dump($position);

$position = strlen($prefix);
var_dump($formatter->parseToCalendar($text, $position));
var_dump($position);

$formatter = new IntlDateFormatter(
    'en_US',
    IntlDateFormatter::NONE,
    IntlDateFormatter::NONE,
    'America/New_York',
    IntlDateFormatter::GREGORIAN,
    'yyyy-MM-dd HH:mm'
);
$formatter->setLenient(false);
$position = 0;
var_dump($formatter->parseToCalendar('2011-03-13 02:30', $position));
var_dump($position);
var_dump(intl_get_error_code());
?>
--EXPECT--
int(1507766400)
int(14)
array(9) {
  ["tm_sec"]=>
  int(0)
  ["tm_min"]=>
  int(0)
  ["tm_hour"]=>
  int(0)
  ["tm_year"]=>
  int(117)
  ["tm_mday"]=>
  int(12)
  ["tm_wday"]=>
  int(4)
  ["tm_yday"]=>
  int(285)
  ["tm_mon"]=>
  int(9)
  ["tm_isdst"]=>
  int(0)
}
int(14)
int(1507766400)
bool(false)
int(16)
int(1)
