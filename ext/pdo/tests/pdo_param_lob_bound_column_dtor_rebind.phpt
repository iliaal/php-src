--TEST--
PDO bound column PARAM_LOB destructor may rebind during fetch
--EXTENSIONS--
pdo
pdo_sqlite
--FILE--
<?php
class BoundColumnRebinder
{
    private PDOStatement $statement;
    private int $column;
    private mixed $replacement;

    public function __construct(PDOStatement $statement, int $column, mixed &$replacement)
    {
        $this->statement = $statement;
        $this->column = $column;
        $this->replacement =& $replacement;
    }

    public function __destruct()
    {
        $this->statement->bindColumn($this->column, $this->replacement, PDO::PARAM_INT);
    }
}

$db = new PDO('sqlite::memory:');

$stmt = $db->prepare('SELECT 42');
$stmt->execute();
$replacement = null;
$bound = new BoundColumnRebinder($stmt, 1, $replacement);
$stmt->bindColumn(1, $bound, PDO::PARAM_LOB);
unset($bound);
var_dump($stmt->fetch(PDO::FETCH_BOUND));
var_dump($replacement);

$stmt = $db->prepare('SELECT 42 AS a, 43 AS b');
$stmt->execute();
$replacement = null;
$bound = new BoundColumnRebinder($stmt, 2, $replacement);
$stmt->bindColumn(1, $bound, PDO::PARAM_LOB);
unset($bound);
var_dump($stmt->fetch(PDO::FETCH_BOUND));
var_dump($replacement);
?>
--EXPECT--
bool(true)
int(42)
bool(true)
int(43)
