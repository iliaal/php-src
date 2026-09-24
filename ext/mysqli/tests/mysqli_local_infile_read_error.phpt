--TEST--
mysqlnd does not terminate LOAD DATA after a local infile read error
--EXTENSIONS--
mysqli
--INI--
mysqli.allow_local_infile=1
--FILE--
<?php
require_once 'fake_server.inc';

class AphmlStream
{
    public $context;
    private int $reads = 0;

    public function stream_open(string $path, string $mode, int $options, ?string &$opened_path): bool
    {
        $this->reads = 0;
        return true;
    }

    public function stream_read(int $count): string|false
    {
        if ($this->reads++ === 0) {
            return "1234\n";
        }
        return false;
    }

    public function stream_eof(): bool
    {
        return false;
    }

    public function stream_close(): void
    {
    }
}

$process = run_fake_server_in_background('local_infile_read_error');
$process->wait();
stream_wrapper_register('aphml', AphmlStream::class);
mysqli_report(MYSQLI_REPORT_OFF);

$conn = new mysqli('127.0.0.1', 'root', '', '', $process->getPort());
$result = $conn->query("LOAD DATA LOCAL INFILE 'ignored' INTO TABLE t");
echo 'Query result: ', var_export($result, true), "\n";
echo 'Client error: ', $conn->error, "\n";
$conn->close();
$process->terminate(true);
stream_wrapper_unregister('aphml');
echo "done!\n";
?>
--EXPECTF--
[*] Server started on 127.0.0.1:%d
[*] Connection established
[*] Sending - Server Greeting: %s
[*] Received: %s
[*] Sending - Server OK: %s
[*] Received: %s
[*] Sending - LOAD DATA LOCAL INFILE request: %s
Query result: false
Client error: Error reading file
[*] Received: %s
[*] LOAD DATA aborted without EOF
[*] Server finished
done!
