--TEST--
SplDoublyLinkedList::serialize() with re-entrant pop() during __serialize()
--FILE--
<?php
class Evil {
    public function __serialize(): array {
        $GLOBALS['list']->pop();
        // churn the heap so freed element/bucket memory is recycled
        for ($i = 0; $i < 20000; $i++) {
            $GLOBALS['junk'][] = [str_repeat('B', 24 + ($i % 128))];
        }
        return ['a' => 1];
    }
}
$list = new SplDoublyLinkedList();
$GLOBALS['list'] = $list;
$list->push([new Evil(), str_repeat('x', 64)]);
$s = @$list->serialize();
$expected = 'i:0;:a:2:{i:0;O:4:"Evil":1:{s:1:"a";i:1;}i:1;s:64:"' . str_repeat('x', 64) . '";}';
echo $s === $expected ? "OK" : "MISMATCH", "\n";
var_dump(strlen($s));
--EXPECT--
OK
int(118)
