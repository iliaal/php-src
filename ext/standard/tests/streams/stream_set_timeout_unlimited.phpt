--TEST--
stream_set_timeout() preserves unlimited socket timeouts
--SKIPIF--
<?php
if (!function_exists('stream_socket_pair')) {
    die("skip stream_socket_pair() is required");
}
?>
--FILE--
<?php
[$reader, $writer] = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);
var_dump(stream_set_timeout($reader, -1));
fwrite($writer, 'unlimited');
var_dump(fread($reader, 9));
var_dump(stream_set_timeout($reader, 1, -2_000_000));
fwrite($writer, 'normalized');
var_dump(fread($reader, 10));
?>
--EXPECT--
bool(true)
string(9) "unlimited"
bool(true)
string(10) "normalized"
