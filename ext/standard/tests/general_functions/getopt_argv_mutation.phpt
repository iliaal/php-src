--TEST--
getopt() captures arguments before string conversion modifies argv
--INI--
register_argc_argv=Off
--FILE--
<?php
$argv = ['test', new class {
    public function __toString(): string {
        $GLOBALS['argv'][2] = '-c';
        return '-a';
    }
}, '-b'];
var_dump(getopt('abc'));
?>
--EXPECT--
array(2) {
  ["a"]=>
  bool(false)
  ["b"]=>
  bool(false)
}
