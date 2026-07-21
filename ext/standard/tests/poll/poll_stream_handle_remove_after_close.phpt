--TEST--
Io\Poll: removing a watcher after its stream is closed unregisters the backend fd (GH-22844)
--SKIPIF--
<?php
if (!Io\Poll\Backend::Poll->isAvailable()) {
    die("skip Poll backend not available\n");
}
?>
--FILE--
<?php
require_once __DIR__ . '/poll.inc';

// The Poll backend keeps a userspace fd->watcher map. If remove() cannot
// unregister the fd (because the stream was already closed) a stale entry is
// left behind and re-hit once that fd number is reused. Auto (epoll) drops
// closed fds in the kernel, so force Poll to exercise the registration path.
$ctx = new Io\Poll\Context(Io\Poll\Backend::Poll);
[$r, $w] = pt_new_socket_pair();

$watcher = $ctx->add(new StreamPollHandle($r), [Io\Poll\Event::Read]);
fclose($r);
fclose($w);

// remove() must still unregister the fd even though the stream is gone.
$watcher->remove();
unset($watcher);
gc_collect_cycles();

// Reclaim the freed fd numbers with fresh readable sockets. A dangling
// registration would map one of these to the freed watcher inside wait().
$keep = [];
for ($i = 0; $i < 6; $i++) {
    [$a, $b] = pt_new_socket_pair();
    fwrite($b, "ping");
    $keep[] = [$a, $b];
}

echo "events: "; var_dump(count($ctx->wait(0, 0)));
echo "done\n";
?>
--EXPECT--
events: int(0)
done
