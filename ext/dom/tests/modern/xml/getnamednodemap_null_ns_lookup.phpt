--TEST--
DOMNamedNodeMap::getNamedItemNS() with null namespace must not match namespaced attributes
--EXTENSIONS--
dom
--FILE--
<?php

$dom = Dom\XMLDocument::createFromString('<root xmlns:foo="http://example.com/foo"/>');
$root = $dom->documentElement;
$root->setAttributeNS('urn:x', 'bar', 'ns');
var_dump($root->attributes->getNamedItemNS(null, 'bar'));
var_dump($root->attributes->getNamedItemNS('urn:x', 'bar')->value);
var_dump(isset($root->attributes['xmlns:foo']));

$other = $dom->createElement('other');
$attrNs = $dom->createAttributeNS('urn:y', 'x:baz');
$attrNs->value = 'nsval';
$other->setAttributeNode($attrNs);
$attrPlain = $dom->createAttribute('baz');
$attrPlain->value = 'plain';
$other->setAttributeNode($attrPlain);
$attrs = $other->attributes;
var_dump($attrs->getNamedItemNS(null, 'baz')->value);
var_dump($attrs->getNamedItemNS('urn:y', 'baz')->value);
var_dump($attrs->getNamedItem('baz')->value);
var_dump($attrs['x:baz']->value);

$html = Dom\HTMLDocument::createFromString('<!DOCTYPE html><html><body><p align="center"></p></body></html>');
$p = $html->getElementsByTagName('p')->item(0);
var_dump($p->attributes->getNamedItemNS(null, 'ALIGN')->value);

?>
--EXPECT--
NULL
string(2) "ns"
bool(true)
string(5) "plain"
string(5) "nsval"
string(5) "plain"
string(5) "nsval"
string(6) "center"
