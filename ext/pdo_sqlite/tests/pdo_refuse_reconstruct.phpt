--TEST--
PDO::__construct refuses re-initialization of a live handle
--EXTENSIONS--
pdo_sqlite
--FILE--
<?php
$p = new PDO('sqlite::memory:');
$p->exec('CREATE TABLE t(i INT)');
$p->exec('INSERT INTO t VALUES (1)');
try {
    $p->__construct('sqlite::memory:');
    echo "re-construct: allowed\n";
} catch (Throwable $e) {
    echo $e::class, ": ", $e->getMessage(), "\n";
}
echo "rows: ", $p->query('SELECT COUNT(*) FROM t')->fetchColumn(), "\n";

$p2 = new PDO('sqlite::memory:', null, null, [PDO::ATTR_PERSISTENT => true]);
try {
    $p2->__construct('sqlite::memory:', null, null, [PDO::ATTR_PERSISTENT => true]);
    echo "persistent re-construct: allowed\n";
} catch (Throwable $e) {
    echo $e::class, ": ", $e->getMessage(), "\n";
}
echo "done\n";
?>
--EXPECT--
Error: PDO object is already initialized
rows: 1
Error: PDO object is already initialized
done
