--TEST--
tidyNode objects are invalid after reparsing their document
--EXTENSIONS--
tidy
--FILE--
<?php

$node = unserialize('O:8:"tidyNode":0:{}');
try {
	$node->hasChildren();
	echo "unowned node: no error\n";
} catch (Error $e) {
	echo 'unowned node: ', $e::class, ': ', $e->getMessage(), "\n";
}

$tidy = tidy_parse_string('<html><body><p>one</p><p>two</p></body></html>');
$node = $tidy->body()->child[0];
var_dump($node->isHtml());
var_dump($node->hasSiblings());

$tidy->parseString('<html><body><p>three</p></body></html>');

$operations = [
    'string cast' => static fn() => (string) $node,
    'hasChildren' => static fn() => $node->hasChildren(),
    'hasSiblings' => static fn() => $node->hasSiblings(),
    'isComment' => static fn() => $node->isComment(),
    'isHtml' => static fn() => $node->isHtml(),
    'isText' => static fn() => $node->isText(),
    'isJste' => static fn() => $node->isJste(),
    'isAsp' => static fn() => $node->isAsp(),
    'isPhp' => static fn() => $node->isPhp(),
    'getParent' => static fn() => $node->getParent(),
    'getPreviousSibling' => static fn() => $node->getPreviousSibling(),
    'getNextSibling' => static fn() => $node->getNextSibling(),
];

foreach ($operations as $operation => $callback) {
    try {
        $callback();
        echo $operation, ": no error\n";
    } catch (Error $e) {
        echo $operation, ': ', $e::class, ': ', $e->getMessage(), "\n";
    }
}

var_dump($tidy->body()->child[0]->isHtml());

?>
--EXPECT--
unowned node: Error: The tidyNode object is no longer valid
bool(true)
bool(true)
string cast: Error: The tidyNode object is no longer valid
hasChildren: Error: The tidyNode object is no longer valid
hasSiblings: Error: The tidyNode object is no longer valid
isComment: Error: The tidyNode object is no longer valid
isHtml: Error: The tidyNode object is no longer valid
isText: Error: The tidyNode object is no longer valid
isJste: Error: The tidyNode object is no longer valid
isAsp: Error: The tidyNode object is no longer valid
isPhp: Error: The tidyNode object is no longer valid
getParent: Error: The tidyNode object is no longer valid
getPreviousSibling: Error: The tidyNode object is no longer valid
getNextSibling: Error: The tidyNode object is no longer valid
bool(true)
