--TEST--
RFC1867 closing boundary at EOF
--SKIPIF--
<?php
if (!getenv('TEST_PHP_CGI_EXECUTABLE')) {
    die("skip php-cgi not available");
}
?>
--FILE--
<?php
$boundary = 'boundary';
$body = "--$boundary\r\nContent-Disposition: form-data; name=\"field\"\r\n\r\nvalue\r\n--$boundary--";
$env = [
    'REDIRECT_STATUS' => '1',
    'CONTENT_TYPE' => "multipart/form-data; boundary=$boundary",
    'CONTENT_LENGTH' => (string) strlen($body),
    'REQUEST_METHOD' => 'POST',
    'SCRIPT_FILENAME' => __DIR__ . '/rfc1867_boundary_eof.inc',
];
$spec = [0 => ['pipe', 'r'], 1 => STDOUT, 2 => STDOUT];
$pipes = [];
$process = proc_open([getenv('TEST_PHP_CGI_EXECUTABLE'), '-C', '-n', __DIR__ . '/rfc1867_boundary_eof.inc'], $spec, $pipes, getcwd(), $env);
fwrite($pipes[0], $body);
fclose($pipes[0]);
proc_close($process);
?>
--EXPECTF--
X-Powered-By: PHP/%s
Content-type: text/html; charset=UTF-8

string(5) "value"
