--TEST--
Dom\HTMLDocument::getElementById() finds ids of SVG and MathML elements
--EXTENSIONS--
dom
--FILE--
<?php
$html = '<!DOCTYPE html><html><body><svg id="s"><rect xml:id="r"/></svg><math id="m"></math><p id="p"></p></body></html>';
$d = Dom\HTMLDocument::createFromString($html, LIBXML_NOERROR);
var_dump([
    'svg #s' => $d->getElementById('s')?->tagName,
    'math #m' => $d->getElementById('m')?->tagName,
    'html #p' => $d->getElementById('p')?->tagName,
    'xml:id #r' => $d->getElementById('r')?->tagName,
]);
?>
--EXPECT--
array(4) {
  ["svg #s"]=>
  string(3) "svg"
  ["math #m"]=>
  string(4) "math"
  ["html #p"]=>
  string(1) "P"
  ["xml:id #r"]=>
  NULL
}
