--TEST--
Session files handler must not truncate session file when write fails
--EXTENSIONS--
session
posix
pcntl
--INI--
error_reporting=E_ALL
display_errors=1
session.use_strict_mode=0
--FILE--
<?php
$dir = sys_get_temp_dir() . '/aph_er5_' . getmypid();
if (!is_dir($dir)) {
    mkdir($dir);
}
foreach (glob($dir . '/sess_*') as $f) {
    unlink($f);
}
ini_set('session.save_path', $dir);

$sid = 'apher5sessid12345678901234567890';
$file = $dir . '/sess_' . $sid;

ob_start();

session_id($sid);
session_start();
$_SESSION['data'] = str_repeat('A', 8192);
session_write_close();

clearstatcache(true);
$before = filesize($file);
var_dump($before > 4096);

pcntl_signal(SIGXFSZ, SIG_IGN);
var_dump(posix_setrlimit(POSIX_RLIMIT_FSIZE, 16, POSIX_RLIMIT_INFINITY));

session_start();
$_SESSION['data'] = str_repeat('B', 4096);
@session_write_close();

clearstatcache(true);
$after = filesize($file);
var_dump($after);
var_dump($after === $before);

ob_end_flush();

foreach (glob($dir . '/sess_*') as $f) {
    @unlink($f);
}
@rmdir($dir);
echo "done\n";
--EXPECT--
bool(true)
bool(true)
int(8207)
bool(true)
done
