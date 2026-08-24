--TEST--
CURLMOPT_PUSHFUNCTION deny must not leak the pushed handle
--EXTENSIONS--
curl
--SKIPIF--
<?php
exec('python3 -c "import h2" 2>/dev/null', $out, $rc);
if ($rc !== 0) {
    die("skip python3 h2 module required");
}

$proc = proc_open(
    ['python3', __DIR__ . '/h2push_server.py'],
    [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
    $pipes
);
if (!is_resource($proc)) {
    die("skip cannot start local HTTP/2 server");
}
$port = (int) trim(fgets($pipes[1]));
$push_count = 0;

function do_request(int $port): void {
    global $push_count;

    $mh = curl_multi_init();
    $callback = function ($parent_ch, $pushed_ch, array $headers) {
        $GLOBALS['push_count']++;
        return CURL_PUSH_DENY;
    };
    curl_multi_setopt($mh, CURLMOPT_PIPELINING, CURLPIPE_MULTIPLEX);
    curl_multi_setopt($mh, CURLMOPT_PUSHFUNCTION, $callback);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://127.0.0.1:$port/");
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2_PRIOR_KNOWLEDGE);
    curl_setopt($ch, CURLOPT_PIPEWAIT, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_multi_add_handle($mh, $ch);

    do {
        curl_multi_exec($mh, $active);
        while (($info = curl_multi_info_read($mh)) !== false) {
            if ($info['msg'] === CURLMSG_DONE) {
                curl_multi_remove_handle($mh, $info['handle']);
                curl_close($info['handle']);
            }
        }
        if (!$active) break;
        curl_multi_select($mh, 1.0);
    } while (true);
    curl_multi_close($mh);
}

do_request($port);
proc_terminate($proc);
proc_close($proc);

if ($push_count === 0) {
    die("skip libcurl received no server push");
}
?>
--FILE--
<?php
$proc = proc_open(
    ['python3', __DIR__ . '/h2push_server.py'],
    [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
    $pipes
);
if (!is_resource($proc)) {
    echo "cannot start local HTTP/2 server\n";
    exit(1);
}
$port = (int) trim(fgets($pipes[1]));
register_shutdown_function(function () use ($proc, $pipes) {
    proc_terminate($proc);
    proc_close($proc);
});

function do_request(int $port): void {
    global $push_count;

    $mh = curl_multi_init();
    $callback = function ($parent_ch, $pushed_ch, array $headers) {
        $GLOBALS['push_count']++;
        return CURL_PUSH_DENY;
    };
    curl_multi_setopt($mh, CURLMOPT_PIPELINING, CURLPIPE_MULTIPLEX);
    curl_multi_setopt($mh, CURLMOPT_PUSHFUNCTION, $callback);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://127.0.0.1:$port/");
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2_PRIOR_KNOWLEDGE);
    curl_setopt($ch, CURLOPT_PIPEWAIT, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_multi_add_handle($mh, $ch);

    $body = null;
    do {
        curl_multi_exec($mh, $active);
        while (($info = curl_multi_info_read($mh)) !== false) {
            if ($info['msg'] === CURLMSG_DONE) {
                $body = curl_multi_getcontent($info['handle']);
                curl_multi_remove_handle($mh, $info['handle']);
                curl_close($info['handle']);
            }
        }
        if ($body !== null || !$active) break;
        curl_multi_select($mh, 1.0);
    } while (true);

    if ($body !== 'BODY') {
        echo "unexpected response body\n";
    }
    curl_multi_close($mh);
}

$iterations = 200;
$bytes_per_iteration_limit = 512;

$push_count = 0;
do_request($port);
gc_collect_cycles();

$before = memory_get_usage();
for ($i = 0; $i < $iterations; $i++) {
    do_request($port);
}
gc_collect_cycles();
$growth = memory_get_usage() - $before;

if ($push_count < $iterations) {
    printf("too few pushes handled: %d\n", $push_count);
} else {
    echo "all pushes denied\n";
}
if ($growth > $iterations * $bytes_per_iteration_limit) {
    printf("leaked %d bytes (%d bytes/iteration)\n", $growth, (int) ($growth / $iterations));
} else {
    echo "no leak detected\n";
}
?>
--EXPECT--
all pushes denied
no leak detected
