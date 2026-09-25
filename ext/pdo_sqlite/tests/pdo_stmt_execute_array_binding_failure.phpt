--TEST--
PDOStatement::execute() preserves existing bindings when array conversion fails
--EXTENSIONS--
pdo_sqlite
--FILE--
<?php

class ThrowingString
{
    public function __toString(): string
    {
        throw new RuntimeException('conversion failed');
    }
}

$db = new PDO('sqlite::memory:');
$stmt = $db->prepare('SELECT :id');
$id = 1;
$stmt->bindParam(':id', $id);

try {
    $stmt->execute([':id' => 2, ':unused' => new ThrowingString()]);
} catch (RuntimeException $e) {
    echo $e->getMessage(), "\n";
}

var_dump($stmt->execute());
var_dump($stmt->fetchColumn());
var_dump($stmt->execute([':id' => 2]));
var_dump($stmt->fetchColumn());
?>
--EXPECT--
conversion failed
bool(true)
string(1) "1"
bool(true)
string(1) "2"
