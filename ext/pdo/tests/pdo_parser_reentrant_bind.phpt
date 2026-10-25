--TEST--
PDO Common: reentrant bound parameter conversion during SQL parsing
--EXTENSIONS--
pdo
--SKIPIF--
<?php
$dir = getenv('REDIR_TEST_DIR');
if ($dir === false) {
    die('skip no driver');
}
require_once $dir . 'pdo_test.inc';
PDOTest::skip();
$db = PDOTest::factory();
try {
    $emulated = (bool) @$db->getAttribute(PDO::ATTR_EMULATE_PREPARES);
} catch (PDOException) {
    $emulated = false;
}
if (!$emulated) {
    try {
        @$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
        $emulated = (bool) @$db->getAttribute(PDO::ATTR_EMULATE_PREPARES);
    } catch (PDOException) {
    }
}
if (!$emulated) {
    die('skip driver cannot emulate prepared statements');
}
?>
--FILE--
<?php
require_once getenv('REDIR_TEST_DIR') . 'pdo_test.inc';

class ReentrantParameter
{
    public function __construct(private PDOStatement $stmt) {}

    public function __toString(): string
    {
        $this->stmt->execute([2, 3]);
        return '1';
    }
}

$db = PDOTest::factory();
$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
$stmt = $db->prepare('SELECT ? AS one, ? AS two');
$stmt->bindValue(1, new ReentrantParameter($stmt), PDO::PARAM_LOB);
$stmt->bindValue(2, 4);
var_dump($stmt->execute());
var_dump($stmt->fetchAll(PDO::FETCH_NUM));
?>
--EXPECT--
bool(true)
array(1) {
  [0]=>
  array(2) {
    [0]=>
    string(1) "1"
    [1]=>
    string(1) "3"
  }
}
