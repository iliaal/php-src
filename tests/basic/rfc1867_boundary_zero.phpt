--TEST--
RFC1867 zero-length file with split closing boundary
--SKIPIF--
<?php
if (!getenv('TEST_PHP_CGI_EXECUTABLE')) {
    die("skip php-cgi not available");
}
?>
--FILE--
<?php
$boundary = str_repeat('A', 5115);
$body = "--$boundary\r\nContent-Disposition: form-data; name=\"file\"; filename=\"test.txt\"\r\nContent-Type: text/plain\r\n\r\n\r\n--$boundary--\r\n";
$env = [
    'REDIRECT_STATUS' => '1',
    'CONTENT_TYPE' => "multipart/form-data; boundary=$boundary",
    'CONTENT_LENGTH' => (string) strlen($body),
    'REQUEST_METHOD' => 'POST',
    'SCRIPT_FILENAME' => __DIR__ . '/rfc1867_boundary_zero.inc',
];
$spec = [0 => ['pipe', 'r'], 1 => STDOUT, 2 => STDOUT];
$pipes = [];
$process = proc_open([getenv('TEST_PHP_CGI_EXECUTABLE'), '-C', '-n', __DIR__ . '/rfc1867_boundary_zero.inc'], $spec, $pipes, getcwd(), $env);
fwrite($pipes[0], $body);
fclose($pipes[0]);
proc_close($process);
?>
--EXPECTF--
X-Powered-By: PHP/%s
Content-type: text/html; charset=UTF-8

int(0)
int(0)
