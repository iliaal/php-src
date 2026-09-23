--TEST--
Proxy-Authorization headers are all removed from request after CONNECT tunnel
--EXTENSIONS--
openssl
--SKIPIF--
<?php require_once 'server.inc'; http_server_skipif(); ?>
--INI--
allow_url_fopen=1
--FILE--
<?php
require_once 'server.inc';

$server = http_server_init($output);

if (is_resource($server)) {
    $conn = stream_socket_accept($server);

    /* Read CONNECT request */
    $req = '';
    while (!str_contains($req, "\r\n\r\n")) {
        $req .= fread($conn, 1024);
    }

    echo "CONNECT contains one Proxy-Authorization: ";
    var_dump(substr_count(strtolower($req), 'proxy-authorization:') === 1);
    echo "CONNECT contains selected Proxy-Authorization: ";
    var_dump(str_contains($req, 'Proxy-Authorization: Basic first'));

    fwrite($conn, "HTTP/1.1 200 Connection established\r\n\r\n");
    fflush($conn);

    stream_context_set_option($conn, 'ssl', 'local_cert', __DIR__ . '/../../../openssl/tests/sni_server.pem');
    stream_socket_enable_crypto($conn, true, STREAM_CRYPTO_METHOD_TLS_SERVER) or die('fail TLS handshake');

    /* Read tunneled request */
    $req2 = '';
    while (!str_contains($req2, "\r\n\r\n")) {
        $req2 .= fread($conn, 1024);
    }

    echo "Proxied request contains no Proxy-Authorization: ";
    var_dump(stripos($req2, 'Proxy-Authorization:') === false);
    echo "Proxied request contains no folded value: ";
    var_dump(stripos($req2, 'continuation') === false);
    echo "Proxied request preserves non-proxy headers: ";
    var_dump(str_contains($req2, 'X-Custom: keep'));

    fwrite($conn,
        "HTTP/1.1 200 OK\r\nContent-Length: 0\r\n\r\n"
    );

    exit;
}

$host = parse_url($server['uri'], PHP_URL_HOST);
$port = parse_url($server['uri'], PHP_URL_PORT);

$ctx = stream_context_create([
    'http' => [
        'proxy' => "tcp://$host:$port",
        'header' => [
            " \tProxy-Authorization: Basic first\r\n\tcontinuation",
            " \tProxy-Authorization: Basic second",
            "Proxy-Authorization: Basic third",
            "X-Custom: keep",
        ],
    ],
    'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false,
    ],
]);

file_get_contents("https://www.php.net/", false, $ctx);

http_server_kill($server['pid']);
?>
--EXPECT--
CONNECT contains one Proxy-Authorization: bool(true)
CONNECT contains selected Proxy-Authorization: bool(true)
Proxied request contains no Proxy-Authorization: bool(true)
Proxied request contains no folded value: bool(true)
Proxied request preserves non-proxy headers: bool(true)
