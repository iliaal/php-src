--TEST--
PDO::setAttribute() validates ATTR_ORACLE_NULLS and ATTR_DEFAULT_FETCH_MODE
--EXTENSIONS--
pdo
pdo_sqlite
--FILE--
<?php
$pdo = new PDO('sqlite::memory:');

foreach ([PDO::NULL_NATURAL, PDO::NULL_EMPTY_STRING, PDO::NULL_TO_STRING] as $value) {
    var_dump($pdo->setAttribute(PDO::ATTR_ORACLE_NULLS, $value));
    var_dump($pdo->getAttribute(PDO::ATTR_ORACLE_NULLS));
}

foreach ([PDO::FETCH_LAZY, PDO::FETCH_ASSOC, PDO::FETCH_KEY_PAIR] as $value) {
    var_dump($pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, $value));
    var_dump($pdo->getAttribute(PDO::ATTR_DEFAULT_FETCH_MODE));
}

foreach ([-1, 3, 4, PHP_INT_MAX] as $value) {
    try {
        $pdo->setAttribute(PDO::ATTR_ORACLE_NULLS, $value);
    } catch (ValueError $e) {
        echo $e::class, ': ', $e->getMessage(), PHP_EOL;
    }
}
var_dump($pdo->getAttribute(PDO::ATTR_ORACLE_NULLS));

$fetch_modes = [[PDO::FETCH_ASSOC], PDO::FETCH_ASSOC | (1 << 21), -1, PDO::FETCH_KEY_PAIR + 1, PHP_INT_MAX];
if (PHP_INT_SIZE === 8) {
    try {
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC | (1 << 32));
        echo "High fetch bit was accepted", PHP_EOL;
    } catch (ValueError $e) {
    }
}
foreach ($fetch_modes as $value) {
    try {
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, $value);
    } catch (Error $e) {
        echo $e::class, ': ', $e->getMessage(), PHP_EOL;
    }
}
var_dump($pdo->getAttribute(PDO::ATTR_DEFAULT_FETCH_MODE));
?>
--EXPECT--
bool(true)
int(0)
bool(true)
int(1)
bool(true)
int(2)
bool(true)
int(1)
bool(true)
int(2)
bool(true)
int(12)
ValueError: PDO::ATTR_ORACLE_NULLS must be one of the PDO::NULL_* constants
ValueError: PDO::ATTR_ORACLE_NULLS must be one of the PDO::NULL_* constants
ValueError: PDO::ATTR_ORACLE_NULLS must be one of the PDO::NULL_* constants
ValueError: PDO::ATTR_ORACLE_NULLS must be one of the PDO::NULL_* constants
int(2)
TypeError: Attribute value must be of type int for selected attribute, array given
ValueError: PDO::ATTR_DEFAULT_FETCH_MODE must be a bitmask of PDO::FETCH_* constants
ValueError: PDO::ATTR_DEFAULT_FETCH_MODE must be a bitmask of PDO::FETCH_* constants
ValueError: PDO::ATTR_DEFAULT_FETCH_MODE must be a bitmask of PDO::FETCH_* constants
ValueError: PDO::ATTR_DEFAULT_FETCH_MODE must be a bitmask of PDO::FETCH_* constants
int(12)
