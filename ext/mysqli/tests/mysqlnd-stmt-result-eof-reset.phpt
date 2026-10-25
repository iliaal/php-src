--TEST--
mysqlnd prepared statement resets safely after result metadata EOF failure
--EXTENSIONS--
mysqli
--FILE--
<?php
function readMysqlPacket($stream): bool
{
    $header = '';
    $deadline = microtime(true) + 2;
    while (strlen($header) < 4 && microtime(true) < $deadline) {
        $read = [$stream];
        $write = [];
        $except = [];
        if (@stream_select($read, $write, $except, 0, 100000) !== 1) {
            continue;
        }
        $chunk = fread($stream, 4 - strlen($header));
        if ($chunk === false || $chunk === '') {
            return false;
        }
        $header .= $chunk;
    }
    if (strlen($header) !== 4) {
        return false;
    }

    $length = unpack('V', $header)[1];
    $payload = '';
    while (strlen($payload) < $length && microtime(true) < $deadline) {
        $read = [$stream];
        $write = [];
        $except = [];
        if (@stream_select($read, $write, $except, 0, 100000) !== 1) {
            continue;
        }
        $chunk = fread($stream, $length - strlen($payload));
        if ($chunk === false || $chunk === '') {
            return false;
        }
        $payload .= $chunk;
    }

    return strlen($payload) === $length;
}

if (($argv[1] ?? '') === 'server') {
    $socket = stream_socket_server('tcp://127.0.0.1:0', $errno, $errstr);
    if ($socket === false) {
        throw new RuntimeException("Failed to create socket: $errstr ($errno)");
    }

    $address = stream_socket_get_name($socket, false);
    fwrite(STDOUT, substr(strrchr($address, ':'), 1) . "\n");
    fflush(STDOUT);

    $stream = stream_socket_accept($socket);
    $send = static function (string $hex) use ($stream): void {
        fwrite($stream, hex2bin($hex));
    };

    $send('580000000a352e352e352d31302e352e31382d4d6172696144420003000000473e3f6047257c6700fef7080200ff81150000000000000f0000006c6b55463f49335f686c6431006d7973716c5f6e61746976655f70617373776f7264');
    readMysqlPacket($stream);
    $send('0700000200000002000000');
    readMysqlPacket($stream);
    $send('0c0000010001000000010000000000003000000203646566087068705f74657374056974656d73056974656d73046974656d046974656d0ce000c8000000fd011000000005000003fe00002200');
    readMysqlPacket($stream);
    $send('01000001013000000203646566087068705f74657374056974656d73056974656d73046974656d046974656d0ce000c8000000fd011000000005000004000003fe0000');

    fclose($stream);
    fclose($socket);
    exit;
}

$process = proc_open(
    [PHP_BINARY, '-n', __FILE__, 'server'],
    [
        0 => ['pipe', 'r'],
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ],
    $pipes
);
if (!is_resource($process)) {
    throw new RuntimeException('Failed to start fake server');
}

fclose($pipes[0]);
$port = (int) trim(fgets($pipes[1]));
if ($port === 0) {
    throw new RuntimeException('Fake server did not provide a port');
}

mysqli_report(MYSQLI_REPORT_OFF);
$connection = new mysqli('127.0.0.1', 'root', '', '', $port);
$statement = $connection->prepare('SELECT item FROM items');
$value = null;
$statement->bind_result($value);

$warnings = [];
set_error_handler(static function (int $severity, string $message) use (&$warnings): bool {
    $warnings[] = $message;
    return true;
});
$executeResult = $statement->execute();
restore_error_handler();

echo 'execute result: ', var_export($executeResult, true), "\n";
echo 'warning emitted: ', var_export($warnings !== [], true), "\n";
echo 'error preserved: ', var_export($statement->error !== '', true), "\n";
echo 'field count reset: ', $statement->field_count, "\n";
echo 'upsert status accessible: ', var_export(is_int($statement->affected_rows), true), "\n";

$statement->close();
$connection->close();

if (is_resource($process)) {
    proc_terminate($process);
}
foreach ($pipes as $pipe) {
    if (is_resource($pipe)) {
        fclose($pipe);
    }
}
if (is_resource($process)) {
    proc_close($process);
}

echo "done\n";
?>
--EXPECT--
execute result: false
warning emitted: true
error preserved: true
field count reset: 0
upsert status accessible: true
done
