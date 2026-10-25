--TEST--
PDOStatement bound parameters survive reentrant string conversion
--EXTENSIONS--
pdo
pdo_sqlite
--FILE--
<?php
$db = new PDO('sqlite::memory:');
$stmt = $db->prepare('SELECT ?');

class ReentrantString
{
    public function __construct(private PDOStatement $stmt)
    {
    }

    public function __toString(): string
    {
        $this->stmt->execute([]);
        return 'outer';
    }
}

$value = new ReentrantString($stmt);
var_dump($stmt->execute([$value]));
var_dump($stmt->fetchColumn());
?>
--EXPECT--
bool(true)
string(5) "outer"
