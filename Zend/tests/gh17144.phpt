--TEST--
GH-17144 (Assertion failure during type inference of ZEND_FETCH_DIM_W)
--INI--
opcache.enable=1
opcache.enable_cli=1
opcache.jit=1254
--EXTENSIONS--
opcache
--FILE--
<?php
function renderRawGraph(array $parents) {
    foreach ($parents as $p) {
        foreach ($graph as $graph) {
            $graph[$graph]['line'] = 1;
        }
        $graph[] = [
            'line' => 1,
        ];
    }
}
echo "Done\n";
?>
--EXPECT--
Done
