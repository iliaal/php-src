--TEST--
SimpleXML::addChild() wrong namespace filter on returned element
--EXTENSIONS--
simplexml
--FILE--
<?php
$x = new SimpleXMLElement('<r xmlns:a="http://example.com"/>');
$c = $x->addChild('a:kid', null, 'http://example.com');
$c->addChild('inner', 'v');
echo trim($x->asXML()), "\n";
echo (string) $c->inner, "\n";
var_dump(isset($c->inner));
$y = new SimpleXMLElement('<r xmlns:a="http://example.com"/>');
$d = $y->addChild('kid', null, 'http://example.com');
$d->addChild('inner', 'w');
echo trim($y->asXML()), "\n";
echo (string) $d->inner, "\n";
?>
--EXPECT--
<?xml version="1.0"?>
<r xmlns:a="http://example.com"><a:kid><a:inner>v</a:inner></a:kid></r>
v
bool(true)
<?xml version="1.0"?>
<r xmlns:a="http://example.com"><a:kid><a:inner>w</a:inner></a:kid></r>
w
