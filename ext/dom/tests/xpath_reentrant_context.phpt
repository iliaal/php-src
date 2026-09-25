--TEST--
DOMXPath: Reentrant evaluation must restore the context node
--EXTENSIONS--
dom
--FILE--
<?php
$doc = new DOMDocument();
$doc->loadXML('<r><a><x>A</x></a><b><x>B</x></b></r>');
$xp = new DOMXPath($doc);
$xp->registerNamespace('php', 'http://php.net/xpath');
$xp->registerPhpFunctions();
$b = $doc->documentElement->lastElementChild;
$GLOBALS['xp'] = $xp;
$GLOBALS['b'] = $b;
function callback($node) {
    $GLOBALS['xp']->query('x', $GLOBALS['b']);
    return true;
}

var_dump($xp->query('a[php:function("callback", .) and x]', $doc->documentElement)->length);
var_dump($xp->evaluate('count(a[php:function("callback", .) and x])', $doc->documentElement));
var_dump($xp->query('a[x]', $doc->documentElement)->length);
?>
--EXPECT--
int(1)
float(1)
int(1)
