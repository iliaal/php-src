--TEST--
GHSA-r6x9-5r99-36j7 (mysqlnd binary row null bitmap truncated)
--EXTENSIONS--
mysqli
--FILE--
<?php
require_once 'fake_server.inc';

$servername = "127.0.0.1";
$username = "root";
$password = "";

$process = run_fake_server_in_background('stmt_response_row_short_null_bitmap');
$process->wait();

$conn = new mysqli($servername, $username, $password, "", $process->getPort());

$stmt = $conn->prepare("SELECT strval, strval FROM data");
$stmt->execute();
$result = $stmt->get_result();
var_dump($result->fetch_row());
$stmt->close();
$conn->close();
$process->terminate();

print "done!";
?>
--EXPECTF--
[*] Server started on 127.0.0.1:%d
[*] Connection established
[*] Sending - Server Greeting: %s
[*] Received: 6900000185a21a00000000c0080000000000000000000000000000000000000000000000726f6f7400006d7973716c5f6e61746976655f70617373776f7264002c0c5f636c69656e745f6e616d65076d7973716c6e640c5f7365727665725f686f7374093132372e302e302e31
[*] Sending - Server OK: 0700000200000002000000
[*] Received: 200000001653454c4543542073747276616c2c2073747276616c2046524f4d2064617461
[*] Sending - Stmt prepare data strval: 0c0000010001000000020000000000003200000203646566087068705f74657374046461746104646174610673747276616c0673747276616c0ce000c8000000fd01100000003200000303646566087068705f74657374046461746104646174610673747276616c0673747276616c0ce000c8000000fd011000000005000004fe00000200
[*] Received: 0a00000017010000000001000000
[*] Sending - Malicious Stmt Response for data strval [null bitmap too short]: 01000001023200000203646566087068705f74657374046461746104646174610673747276616c0673747276616c0ce000c8000000fd01100000003200000303646566087068705f74657374046461746104646174610673747276616c0673747276616c0ce000c8000000fd011000000005000004fe00002200010000050005000006fe00002200

Warning: mysqli_result::fetch_row(): Malformed server packet. No packet space left for the null bitmap in %s on line %d
bool(false)
done!
