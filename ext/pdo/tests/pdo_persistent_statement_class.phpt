--TEST--
PDO persistent connection rejects ATTR_STATEMENT_CLASS with one warning
--EXTENSIONS--
pdo
pdo_sqlite
--FILE--
<?php
$db = new PDO('sqlite::memory:', null, null, [PDO::ATTR_PERSISTENT => true]);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
$warnings = [];

set_error_handler(function (int $severity, string $message) use (&$warnings): bool {
    $warnings[] = $message;
    return true;
});

try {
    $result = $db->setAttribute(PDO::ATTR_STATEMENT_CLASS, [PDOStatement::class]);
} finally {
    restore_error_handler();
}
var_dump($result);
var_dump(count($warnings));
echo $warnings[0], "\n";
?>
--EXPECT--
bool(false)
int(1)
PDO::setAttribute(): SQLSTATE[HY000]: General error: PDO::ATTR_STATEMENT_CLASS cannot be used with persistent PDO instances
