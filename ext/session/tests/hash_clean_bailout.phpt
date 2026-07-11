--TEST--
Use-after-free when a destructor bails out during zend_hash_clean() via session_unset()
--EXTENSIONS--
session
--SKIPIF--
<?php include('skipif.inc'); ?>
--INI--
memory_limit=32M
session.save_handler=files
session.use_cookies=0
session.cache_limiter=
--FILE--
<?php
class Dtor {
    public function __destruct() {}
}
class Killer extends php_user_filter {
    public function onClose(): void {
        $x = str_repeat('A', 256 * 1024 * 1024);
    }
    public function filter($in, $out, &$consumed, $closing): int {
        return PSFS_PASS_ON;
    }
}
stream_filter_register('killer', Killer::class);

session_start();
$_SESSION['obj'] = new Dtor();
$fp = fopen('php://memory', 'r+');
stream_filter_append($fp, 'killer');
$_SESSION['res'] = $fp;
unset($fp);
session_unset();
echo "done\n";
?>
--EXPECTF--
Fatal error: Allowed memory size of %d bytes exhausted%a
