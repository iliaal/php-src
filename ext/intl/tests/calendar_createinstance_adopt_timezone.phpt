--TEST--
IntlCalendar::createInstance() and fromDateTime() adopt the TimeZone
--EXTENSIONS--
intl
--INI--
date.timezone=UTC
--FILE--
<?php

$cal = IntlCalendar::createInstance('Europe/Amsterdam', 'en_US');
echo $cal->getTimeZone()->getID(), "\n";
echo $cal->getType(), "\n";

$dt = new DateTime('2024-01-15 12:00:00', new DateTimeZone('America/New_York'));
$cal2 = IntlCalendar::fromDateTime($dt, 'en_US');
echo $cal2->getTimeZone()->getID(), "\n";
echo $cal2->getType(), "\n";

?>
--EXPECT--
Europe/Amsterdam
gregorian
America/New_York
gregorian
