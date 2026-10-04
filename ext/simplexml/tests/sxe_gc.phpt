--TEST--
SimpleXML exposes iterator and temporary references to the cycle collector
--EXTENSIONS--
simplexml
--FILE--
<?php

$sxe = new SimpleXMLElement('<root><child/></root>');
$child =& $sxe->child;
$sxe->rewind();
gc_collect_cycles();
unset($child, $sxe);

echo "done\n";

?>
--EXPECT--
done
