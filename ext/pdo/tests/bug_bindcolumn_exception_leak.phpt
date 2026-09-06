--TEST--
PDO: bindColumn() must not register the binding when an exception is thrown for an unknown column name
--EXTENSIONS--
pdo
pdo_sqlite
--FILE--
<?php
$db = new PDO('sqlite::memory:');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$stmt = $db->prepare('SELECT 1 AS foo');
$stmt->execute();
$var = null;
$n = 200000;
for ($i = 0; $i < $n; $i++) {
    try {
        $stmt->bindColumn("bogus$i", $var);
    } catch (PDOException $e) {
    }
}
$before = memory_get_usage();
for ($i = 0; $i < $n; $i++) {
    try {
        $stmt->bindColumn("bogus$i", $var);
    } catch (PDOException $e) {
    }
}
$diff = memory_get_usage() - $before;
if ($diff > 1000) {
    echo "LEAK\n";
} else {
    echo "OK\n";
}
?>
--EXPECT--
OK
