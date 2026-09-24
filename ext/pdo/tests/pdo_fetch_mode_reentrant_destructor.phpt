--TEST--
PDO SQLite: setFetchMode() publishes state before releasing old owners
--EXTENSIONS--
pdo
pdo_sqlite
--FILE--
<?php
class ReenterIntoOwner
{
    public static ?Closure $callback = null;

    public function __destruct()
    {
        echo "old into owner destructed\n";
        (self::$callback)();
    }
}

class ReenterClassArgument
{
    public static ?Closure $callback = null;

    public function __destruct()
    {
        echo "old class argument destructed\n";
        (self::$callback)();
    }
}

class FetchClassRow
{
    public int $value;

    public function __construct(string $marker)
    {
    }
}

$db = new PDO('sqlite::memory:');
$db->exec('CREATE TABLE fetch_mode_reentry (value INTEGER)');
$db->exec('INSERT INTO fetch_mode_reentry VALUES (1), (2)');

$stmt = $db->query('SELECT value FROM fetch_mode_reentry ORDER BY value');
$replacement = new stdClass;
$old = new ReenterIntoOwner;
ReenterIntoOwner::$callback = function () use ($stmt, $replacement): void {
    $stmt->setFetchMode(PDO::FETCH_INTO, $replacement);
    echo "into re-entry complete\n";
};
$stmt->setFetchMode(PDO::FETCH_INTO, $old);
unset($old);
$stmt->setFetchMode(PDO::FETCH_NUM);
ReenterIntoOwner::$callback = null;

$row = $stmt->fetch();
echo "into re-entry active: ";
var_dump($row === $replacement);
echo "into re-entry value: ", $row->value, "\n";
$weak = WeakReference::create($replacement);
unset($row);
unset($replacement, $stmt);
gc_collect_cycles();
echo "into owner released: ";
var_dump($weak->get() === null);

$stmt = $db->query('SELECT value FROM fetch_mode_reentry ORDER BY value');
$replacement = new stdClass;
$argument = new ReenterClassArgument;
ReenterClassArgument::$callback = function () use ($stmt, $replacement): void {
    $stmt->setFetchMode(PDO::FETCH_INTO, $replacement);
    echo "class re-entry complete\n";
};
$stmt->setFetchMode(PDO::FETCH_CLASS, FetchClassRow::class, [$argument]);
unset($argument);
$stmt->setFetchMode(PDO::FETCH_ASSOC);
ReenterClassArgument::$callback = null;

$row = $stmt->fetch();
echo "class re-entry active: ";
var_dump($row === $replacement);
echo "class re-entry value: ", $row->value, "\n";
$weak = WeakReference::create($replacement);
unset($row);
unset($replacement, $stmt);
gc_collect_cycles();
echo "class owner released: ";
var_dump($weak->get() === null);
?>
--EXPECT--
old into owner destructed
into re-entry complete
into re-entry active: bool(true)
into re-entry value: 1
into owner released: bool(true)
old class argument destructed
class re-entry complete
class re-entry active: bool(true)
class re-entry value: 1
class owner released: bool(true)
