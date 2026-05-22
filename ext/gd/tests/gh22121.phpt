--TEST--
GH-22121 (Double free in gdImageSetStyle() after overflow-triggered early return)
--EXTENSIONS--
gd
--INI--
memory_limit=-1
--SKIPIF--
<?php
if (!getenv('RUN_RESOURCE_HEAVY_TESTS')) die('skip resource-heavy test');
if (PHP_INT_SIZE < 8) die('skip 64-bit only (allocates ~10 GiB)');
function get_system_memory(): int|float|false {
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        @exec('wmic OS get FreePhysicalMemory', $output);
        if (isset($output[1])) {
            return ((int)trim($output[1])) * 1024;
        }
    } else {
        $memInfo = @file_get_contents("/proc/meminfo");
        if ($memInfo && preg_match('/MemAvailable:\s+(\d+) kB/', $memInfo, $matches)) {
            return $matches[1] * 1024;
        }
    }
    return false;
}
if (get_system_memory() < 12 * 1024 * 1024 * 1024) {
    die('skip Reason: Insufficient RAM (less than 12GB)');
}
?>
--FILE--
<?php
$im = imagecreatetruecolor(1, 1);
imagesetstyle($im, [0]);
imagesetstyle($im, array_fill(0, 536870912, 0));
unset($im);
echo "no double free\n";
?>
--EXPECTF--
Warning: imagesetstyle(): Product of memory allocation multiplication would exceed INT_MAX, failing operation gracefully
 in %s on line %d
no double free
