--TEST--
Persistent Pdo\Pgsql notice callbacks remain alive during garbage collection
--EXTENSIONS--
pdo
pdo_pgsql
--SKIPIF--
<?php
require __DIR__ . '/config.inc';
require dirname(__DIR__, 2) . '/pdo/tests/pdo_test.inc';
PDOTest::skip();
?>
--FILE--
<?php
require __DIR__ . '/config.inc';
require dirname(__DIR__, 2) . '/pdo/tests/pdo_test.inc';

putenv('PDOTEST_ATTR=' . serialize([PDO::ATTR_PERSISTENT => true]));
foreach ([1, 2] as $count) {
    $connections = [];
    for ($i = 0; $i < $count; $i++) {
        $connections[] = PDOTest::factory(Pdo\Pgsql::class);
    }
    $connections[0]->setNoticeCallback(function ($message) use ($connections) {
        echo trim($message), "\n";
    });
    gc_collect_cycles();
    $connections[0]->exec("DO $$ BEGIN RAISE NOTICE 'live $count'; END $$");
    unset($connections);
    gc_collect_cycles();
    $db = PDOTest::factory(Pdo\Pgsql::class);
    $db->exec("DO $$ BEGIN RAISE NOTICE 'reused $count'; END $$");
    $db->setNoticeCallback(null);
    unset($db);
    gc_collect_cycles();
}
?>
--EXPECT--
NOTICE:  live 1
NOTICE:  reused 1
NOTICE:  live 2
NOTICE:  reused 2
