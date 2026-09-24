--TEST--
PDO SQLite: setFetchMode() autoload re-entry observes the prior fetch state
--EXTENSIONS--
pdo
pdo_sqlite
--FILE--
<?php
class ExistingFetchClass
{
    public int $value;

    public function __construct(public string $marker)
    {
    }
}

$db = new PDO('sqlite::memory:');
$db->exec('CREATE TABLE fetch_mode_autoload_reentry (value INTEGER)');
$db->exec('INSERT INTO fetch_mode_autoload_reentry VALUES (1), (2)');

$stmt = $db->query('SELECT value FROM fetch_mode_autoload_reentry ORDER BY value');
$into = new stdClass;
$stmt->setFetchMode(PDO::FETCH_INTO, $into);
$autoload = function (string $class) use ($stmt, $into): void {
    $row = $stmt->fetch();
    echo "into state preserved: ";
    var_dump($row === $into);
    echo "into re-entry value: ", $into->value, "\n";
    eval("class $class { public int \$value; }");
};
spl_autoload_register($autoload);
$stmt->setFetchMode(PDO::FETCH_CLASS, 'LateIntoClass');
spl_autoload_unregister($autoload);
$row = $stmt->fetch();
echo "outer into class: ", $row::class, " ", $row->value, "\n";

$stmt = $db->query('SELECT value FROM fetch_mode_autoload_reentry ORDER BY value');
$stmt->setFetchMode(PDO::FETCH_CLASS, ExistingFetchClass::class, ['kept']);
$autoload = function (string $class) use ($stmt): void {
    $row = $stmt->fetch();
    echo "class state preserved: ", $row::class, " ", $row->marker, " ", $row->value, "\n";
    eval("class $class { public int \$value; }");
};
spl_autoload_register($autoload);
$stmt->setFetchMode(PDO::FETCH_CLASS, 'LateFetchClass');
spl_autoload_unregister($autoload);
$row = $stmt->fetch();
echo "outer class: ", $row::class, " ", $row->value, "\n";

$stmt = $db->query('SELECT value FROM fetch_mode_autoload_reentry');
$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, 0x7fffffff);
try {
    $stmt->setFetchMode(PDO::FETCH_DEFAULT);
} catch (ValueError $e) {
    echo "invalid dbh default: ", $e::class, "\n";
}
?>
--EXPECT--
into state preserved: bool(true)
into re-entry value: 1
outer into class: LateIntoClass 2
class state preserved: ExistingFetchClass kept 1
outer class: LateFetchClass 2
invalid dbh default: ValueError
