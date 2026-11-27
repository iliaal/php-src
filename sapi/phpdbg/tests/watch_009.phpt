--TEST--
Deleting a recursive watchpoint after its variable became an array
--PHPDBG--
b 4
r
w r $b
c
w d 0
c
q
--EXPECTF--
[Successful compilation of %s]
prompt> [Breakpoint #0 added at %s:4]
prompt> [Breakpoint #0 at %s:4, hits: 1]
>00004: $b = [1, [2]];
 00005: $c = 1;
 00006: 
prompt> [Added recursive watchpoint #0 for $b]
prompt> [Breaking on watchpoint $b]
Old value: 
New value: Array ([0] => 1,[1] => Array ([0] => 2))
>00005: $c = 1;
 00006: 
prompt> [Removed watchpoint 0]
prompt> [Script ended normally]
prompt> 
--FILE--
<?php

$b = null;
$b = [1, [2]];
$c = 1;
