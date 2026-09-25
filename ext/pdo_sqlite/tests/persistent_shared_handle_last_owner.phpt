--TEST--
PDO SQLite persistent shared handle teardown waits for the last owner
--EXTENSIONS--
pdo_sqlite
--FILE--
<?php
$dbfile = __DIR__ . '/persistent_shared_handle_last_owner.sqlite';
$options = [PDO::ATTR_PERSISTENT => true];

function pdo_shared_owner_udf(): int
{
    return 42;
}

$first = new PDO('sqlite:' . $dbfile, null, null, $options);
$second = new PDO('sqlite:' . $dbfile, null, null, $options);

$first->exec('CREATE TABLE test (value INTEGER)');
$first->sqliteCreateFunction('pdo_shared_owner_udf', 'pdo_shared_owner_udf');
$first->beginTransaction();
$first->exec('INSERT INTO test VALUES (1)');

unset($first);

try {
    echo 'UDF after first owner destroyed: ', $second->query('SELECT pdo_shared_owner_udf()')->fetchColumn(), "\n";
} catch (PDOException $e) {
    echo 'UDF after first owner destroyed: ', $e::class, ': ', $e->getMessage(), "\n";
}

echo 'In transaction after first owner destroyed: ';
var_dump($second->inTransaction());
if ($second->inTransaction()) {
    $second->commit();
}

echo 'Rows after commit: ', $second->query('SELECT COUNT(*) FROM test')->fetchColumn(), "\n";
?>
--CLEAN--
<?php
$dbfile = __DIR__ . '/persistent_shared_handle_last_owner.sqlite';
@unlink($dbfile);
?>
--EXPECT--
UDF after first owner destroyed: 42
In transaction after first owner destroyed: bool(true)
Rows after commit: 1
