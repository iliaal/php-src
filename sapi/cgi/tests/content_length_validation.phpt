--TEST--
CGI and FastCGI CONTENT_LENGTH validation
--SKIPIF--
<?php
include "skipif.inc";
?>
--FILE--
<?php
include "include.inc";

function run_cgi(string $php, string $script, string $content_length, string $body): array
{
    $env = getenv();
    $env['GATEWAY_INTERFACE'] = 'CGI/1.1';
    $env['REQUEST_METHOD'] = 'POST';
    $env['CONTENT_TYPE'] = 'application/octet-stream';
    $env['CONTENT_LENGTH'] = $content_length;
    $env['SCRIPT_FILENAME'] = $script;
    $env['SCRIPT_NAME'] = $script;
    $env['PATH_TRANSLATED'] = $script;
    $env['QUERY_STRING'] = '';
    $env['REDIRECT_STATUS'] = '1';

    $process = proc_open(
        [$php, '-n', '-d', 'expose_php=0', $script],
        [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ],
        $pipes,
        null,
        $env
    );
    if (!is_resource($process)) {
        throw new RuntimeException('Failed to start php-cgi');
    }

    fwrite($pipes[0], $body);
    fclose($pipes[0]);
    $stdout = stream_get_contents($pipes[1]);
    fclose($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[2]);

    $header_end = strpos($stdout, "\r\n\r\n");
    if ($header_end !== false) {
        $stdout = substr($stdout, $header_end + 4);
    }

    return [proc_close($process), $stdout, $stderr];
}

$php = get_cgi_path();
$script = tempnam(sys_get_temp_dir(), 'cgi-content-length-');
file_put_contents($script, '<?php header_remove(); echo file_get_contents("php://input");');

$cases = [
    ['3', 'abc'],
    ['0003', 'abc'],
    ['0', ''],
    ['', ''],
    ['-1', ''],
    ['+3', ''],
    ['3garbage', ''],
    [' 3', ''],
    ['3 ', ''],
    ['9223372036854775808', ''],
    ['18446744073709551615', ''],
];

foreach ($cases as [$content_length, $expected_body]) {
    [$status, $stdout, $stderr] = run_cgi($php, $script, $content_length, 'abc');
    printf(
        "%s: exit=%d body=%s stderr=%s\n",
        var_export($content_length, true),
        $status,
        var_export($stdout, true),
        var_export($stderr, true)
    );
    if ($status !== 0 || $stdout !== $expected_body || $stderr !== '') {
        printf("Expected body %s\n", var_export($expected_body, true));
    }
}

unlink($script);
?>
--EXPECT--
'3': exit=0 body='abc' stderr=''
'0003': exit=0 body='abc' stderr=''
'0': exit=0 body='' stderr=''
'': exit=0 body='' stderr=''
'-1': exit=0 body='' stderr=''
'+3': exit=0 body='' stderr=''
'3garbage': exit=0 body='' stderr=''
' 3': exit=0 body='' stderr=''
'3 ': exit=0 body='' stderr=''
'9223372036854775808': exit=0 body='' stderr=''
'18446744073709551615': exit=0 body='' stderr=''
