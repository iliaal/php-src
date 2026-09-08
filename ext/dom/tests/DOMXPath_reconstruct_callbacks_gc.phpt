--TEST--
Re-constructing a DOMXPath does not expose freed callback registrations to the cycle collector
--EXTENSIONS--
dom
--FILE--
<?php
class GcElement extends DOMElement
{
    public function __destruct()
    {
        gc_collect_cycles();
    }
}

$doc = new DOMDocument();
$doc->loadXML('<r><a/><b/><c/></r>');
$doc->registerNodeClass(DOMElement::class, GcElement::class);

$xp = new DOMXPath($doc);
$xp->registerNamespace('php', 'http://php.net/xpath');
$xp->registerPhpFunctions();

function cb($n) {
    return true;
}

$xp->query('/r/*[php:function("cb", .)]');

/* Make the object a collector root candidate, then re-construct it: the
   registration teardown must not stay reachable while it is being freed. */
$tmp = $xp;
unset($tmp);
$xp->__construct($doc);

var_dump($xp->query('/r/a')->length);
echo 'done', PHP_EOL;
?>
--EXPECT--
int(1)
done
