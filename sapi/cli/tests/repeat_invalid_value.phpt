--TEST--
CLI --repeat with a value below 1 must fail fast instead of looping forever
--FILE--
<?php
$php = escapeshellarg(PHP_BINARY);
foreach (array('0', '-2', 'abc', '99999999999999999999999') as $value) {
    $cmd = sprintf('timeout 5 %s --repeat=%s -r %s 2>&1 | head -c 200',
        $php, escapeshellarg($value), escapeshellarg('echo "ran\n";'));
    $out = trim((string) shell_exec($cmd));
    echo $value, ' => ', substr($out, 0, 60), "\n";
}
?>
--EXPECT--
0 => Value of --repeat must be a positive integer.
-2 => Value of --repeat must be a positive integer.
abc => Value of --repeat must be a positive integer.
99999999999999999999999 => Value of --repeat must be a positive integer.
