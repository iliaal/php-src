--TEST--
DOMXPath callback arguments/results must not be pinned by node_list after evaluation
--EXTENSIONS--
dom
--FILE--
<?php
class Holder {
    public $node;
}

$doc = new DOMDocument();
$doc->loadXML('<r><a/><b/></r>');
$xp = new DOMXPath($doc);
$xp->registerNamespace('php', 'http://php.net/xpath');
$xp->registerPhpFunctions();

$GLOBALS['plain_wr'] = null;
$GLOBALS['cyclic_wr'] = null;

function cb($n) {
    $GLOBALS['plain_wr'] = WeakReference::create($n[0]);
    return true;
}

function cb_cyclic($n) {
    $GLOBALS['cyclic_wr'] = WeakReference::create($n[0]);
    $holder = new Holder();
    @$n[0]->owner = $holder;
    $holder->node = $n[0];
    return true;
}

$xp->query('/r/a[php:function("cb", .)]');

echo "plain: alive right after eval = ";
var_export($GLOBALS['plain_wr']->get() !== null);
echo "\n";

$xp->query('/r/a[php:function("cb_cyclic", .)]');

echo "cyclic: alive right after eval = ";
var_export($GLOBALS['cyclic_wr']->get() !== null);
echo "\n";

$n = gc_collect_cycles();

echo "plain: alive after gc = ";
var_export($GLOBALS['plain_wr']->get() !== null);
echo "\n";
echo "cyclic: alive after gc = ";
var_export($GLOBALS['cyclic_wr']->get() !== null);
echo "\n";

if ($GLOBALS['plain_wr']->get() !== null || $GLOBALS['cyclic_wr']->get() !== null) {
    echo "LEAK: node pinned solely by unpinned-in-GC node_list\n";
}
?>
--EXPECT--
plain: alive right after eval = false
cyclic: alive right after eval = true
plain: alive after gc = false
cyclic: alive after gc = false
