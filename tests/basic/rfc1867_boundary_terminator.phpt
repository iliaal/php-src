--TEST--
RFC1867 boundary terminator validation and compatibility
--SKIPIF--
<?php
if (!getenv('TEST_PHP_CGI_EXECUTABLE')) {
    die("skip php-cgi not available");
}
?>
--FILE--
<?php
$boundary = 'boundary';
$cases = [
    ["prefix\r\n--{$boundary}X\r\n", 19],
    ["lf\n--$boundary\n", 2],
    ["padded \r\n--$boundary \r\n", 7],
    ["padded close\r\n--$boundary-- \r\n", 12],
    [str_repeat('A', 5100) . "\r\n--{$boundary}X\r\n", 5113],
    [str_repeat('A', 5100) . "\r\n", 5100],
    [str_repeat('A', 5100) . "\r\n--{$boundary} \r\n", 5100],
];
$spec = [0 => ['pipe', 'r'], 1 => STDOUT, 2 => STDOUT];
foreach ($cases as [$value, $expected]) {
    $body = "--$boundary\r\nContent-Disposition: form-data; name=\"field\"\r\n\r\n$value--$boundary--\r\n";
    $env = [
        'REDIRECT_STATUS' => '1',
        'CONTENT_TYPE' => "multipart/form-data; boundary=$boundary",
        'CONTENT_LENGTH' => (string) strlen($body),
        'REQUEST_METHOD' => 'POST',
        'SCRIPT_FILENAME' => __DIR__ . '/rfc1867_boundary_terminator.inc',
    ];
    $pipes = [];
    $process = proc_open([getenv('TEST_PHP_CGI_EXECUTABLE'), '-C', '-n', __DIR__ . '/rfc1867_boundary_terminator.inc'], $spec, $pipes, getcwd(), $env);
    fwrite($pipes[0], $body);
    fclose($pipes[0]);
    proc_close($process);
}
?>
--EXPECTF--
X-Powered-By: PHP/%s
Content-type: text/html; charset=UTF-8

field 19
X-Powered-By: PHP/%s
Content-type: text/html; charset=UTF-8

field 2
X-Powered-By: PHP/%s
Content-type: text/html; charset=UTF-8

field 7
X-Powered-By: PHP/%s
Content-type: text/html; charset=UTF-8

field 12
X-Powered-By: PHP/%s
Content-type: text/html; charset=UTF-8

field 5113
X-Powered-By: PHP/%s
Content-type: text/html; charset=UTF-8

field 5100
X-Powered-By: PHP/%s
Content-type: text/html; charset=UTF-8

field 5100
