--TEST--
Recursive watchpoint on an array is released with its children at shutdown
--PHPDBG--
b 5
r
w $x
w r $a
c
c
q
--EXPECTF--
[Successful compilation of %s]
prompt> [Breakpoint #0 added at %s:5]
prompt> [Breakpoint #0 at %s:5, hits: 1]
>00005: $x = 2;
 00006: 
prompt> [Added watchpoint #0 for $x]
prompt> [Added recursive watchpoint #1 for $a]
prompt> [Breaking on watchpoint $x]
Old value: 1
New value: 2
>00006: 
prompt> [$x has been removed, removing watchpoint]
[$a has been removed, removing watchpoint recursively]
[Script ended normally]
prompt> 
--FILE--
<?php

$x = 1;
$a = [1, [2]];
$x = 2;
