--TEST--
Collator::sortWithSortKeys() buffer allocation scales with array size
--EXTENSIONS--
intl
--FILE--
<?php
$c = new Collator('en_US');
$a = ['bb', 'aa', 'dd', 'cc'];
$c->sort($a);

$before = memory_get_peak_usage();
$b = ['bb', 'aa', 'cc', 'ab', 'ca', 'bc', 'ac', 'ba'];
$c->sortWithSortKeys($b);
$peakDelta = memory_get_peak_usage() - $before;

var_dump($a);
var_dump($b);
var_dump($peakDelta < 100000);
?>
--EXPECT--
array(4) {
  [0]=>
  string(2) "aa"
  [1]=>
  string(2) "bb"
  [2]=>
  string(2) "cc"
  [3]=>
  string(2) "dd"
}
array(8) {
  [0]=>
  string(2) "aa"
  [1]=>
  string(2) "ab"
  [2]=>
  string(2) "ac"
  [3]=>
  string(2) "ba"
  [4]=>
  string(2) "bb"
  [5]=>
  string(2) "bc"
  [6]=>
  string(2) "ca"
  [7]=>
  string(2) "cc"
}
bool(true)
