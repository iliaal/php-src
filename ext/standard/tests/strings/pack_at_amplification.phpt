--TEST--
pack() must reject pathological @/x output amplification
--FILE--
<?php
try {
    pack("@2147483647");
    echo "no throw\n";
} catch (ValueError $e) {
    echo "ValueError: ", $e->getMessage(), "\n";
}
try {
    pack("x2147483647");
    echo "no throw x\n";
} catch (ValueError $e) {
    echo "ValueError: ", $e->getMessage(), "\n";
}
$s = pack("x1000");
echo strlen($s), "\n";
?>
--EXPECT--
ValueError: pack(): string is too large
ValueError: pack(): string is too large
1000
