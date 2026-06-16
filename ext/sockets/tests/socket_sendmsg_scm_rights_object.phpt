--TEST--
socket_sendmsg(): SCM_RIGHTS transfers a Socket object with the correct fd
--EXTENSIONS--
sockets
--SKIPIF--
<?php
if (strtolower(substr(PHP_OS, 0, 3)) == 'win') {
    die('skip not for Microsoft Windows');
}
if (strtolower(substr(PHP_OS, 0, 3)) == 'aix') {
    die('skip not for AIX');
}
if (!defined('SCM_RIGHTS')) {
    die('skip SCM_RIGHTS not available');
}
?>
--FILE--
<?php
$dir = sys_get_temp_dir();
$rpath = $dir . "/socket_sendmsg_scm_rights_object_r.sock";
$ppath = $dir . "/socket_sendmsg_scm_rights_object_p.sock";
@unlink($rpath);
@unlink($ppath);

$recv = socket_create(AF_UNIX, SOCK_DGRAM, 0);
socket_bind($recv, $rpath);

$send = socket_create(AF_UNIX, SOCK_DGRAM, 0);
socket_connect($send, $rpath);

// Pass a single Socket object, bound to a known path so the received fd
// can be identified via getsockname().
$payload = socket_create(AF_UNIX, SOCK_DGRAM, 0);
socket_bind($payload, $ppath);

socket_sendmsg($send, [
    'iov' => ['x'],
    'control' => [[
        'level' => SOL_SOCKET,
        'type' => SCM_RIGHTS,
        'data' => [$payload],
    ]],
], 0);

$data = [
    'name' => [],
    'buffer_size' => 64,
    'controllen' => socket_cmsg_space(SOL_SOCKET, SCM_RIGHTS, 1),
];
socket_recvmsg($recv, $data, 0);

$got = $data['control'][0]['data'][0];
var_dump($got instanceof Socket);
socket_getsockname($got, $addr);
var_dump($addr === $ppath);
?>
--CLEAN--
<?php
$dir = sys_get_temp_dir();
@unlink($dir . "/socket_sendmsg_scm_rights_object_r.sock");
@unlink($dir . "/socket_sendmsg_scm_rights_object_p.sock");
?>
--EXPECT--
bool(true)
bool(true)
