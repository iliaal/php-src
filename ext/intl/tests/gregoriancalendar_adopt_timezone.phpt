--TEST--
IntlGregorianCalendar timezone-and-locale constructor adopts the TimeZone
--EXTENSIONS--
intl
--INI--
date.timezone=UTC
--FILE--
<?php

$cal = new IntlGregorianCalendar(new DateTimeZone('Europe/Amsterdam'), 'en_US');
echo $cal->getTimeZone()->getID(), "\n";
echo $cal->getType(), "\n";

$cal2 = IntlGregorianCalendar::createInstance('UTC', 'en_US');
echo $cal2->getTimeZone()->getID(), "\n";

?>
--EXPECT--
Europe/Amsterdam
gregorian
UTC
