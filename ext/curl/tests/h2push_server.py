import socket
import sys

from h2.config import H2Configuration
from h2.connection import H2Connection
from h2.events import RequestReceived, StreamReset

sock = socket.socket()
sock.setsockopt(socket.SOL_SOCKET, socket.SO_REUSEADDR, 1)
sock.bind(("127.0.0.1", 0))
sock.listen(8)
print(sock.getsockname()[1], flush=True)
authority = "127.0.0.1:%d" % sock.getsockname()[1]

config = H2Configuration(client_side=False)

while True:
    conn, addr = sock.accept()
    try:
        h2c = H2Connection(config=config)
        h2c.initiate_connection()
        conn.sendall(h2c.data_to_send())
        while True:
            data = conn.recv(65535)
            if not data:
                break
            events = h2c.receive_data(data)
            for event in events:
                if isinstance(event, RequestReceived):
                    sid = event.stream_id
                    psid = h2c.get_next_available_stream_id()
                    h2c.push_stream(sid, psid, [
                        (":method", "GET"),
                        (":path", "/pushed"),
                        (":scheme", "http"),
                        (":authority", authority),
                    ])
                    h2c.send_headers(sid, [
                        (":status", "200"),
                        ("content-type", "text/plain"),
                        ("content-length", "4"),
                    ])
                    h2c.send_data(sid, b"BODY", end_stream=True)
                    h2c.send_headers(psid, [
                        (":status", "200"),
                        ("content-type", "text/plain"),
                        ("content-length", "6"),
                    ])
                    h2c.send_data(psid, b"PUSHED", end_stream=True)
                elif isinstance(event, StreamReset):
                    pass
            out = h2c.data_to_send()
            if out:
                conn.sendall(out)
    except Exception:
        pass
    finally:
        try:
            conn.close()
        except OSError:
            pass
