--TEST--
Collator attribute and strength methods on unconstructed object
--EXTENSIONS--
intl
--FILE--
<?php
class C extends Collator {
    public function __construct() {
        // omitting parent::__construct();
    }
}
$c = new C();

try {
    var_dump($c->getAttribute(Collator::NUMERIC_COLLATION));
} catch (Error $e) {
    echo get_class($e), ": ", $e->getMessage(), "\n";
}

try {
    var_dump($c->setAttribute(Collator::NUMERIC_COLLATION, Collator::ON));
} catch (Error $e) {
    echo get_class($e), ": ", $e->getMessage(), "\n";
}

try {
    var_dump($c->getStrength());
} catch (Error $e) {
    echo get_class($e), ": ", $e->getMessage(), "\n";
}

try {
    var_dump($c->setStrength(Collator::SECONDARY));
} catch (Error $e) {
    echo get_class($e), ": ", $e->getMessage(), "\n";
}
?>
--EXPECT--
Error: Object not initialized
Error: Object not initialized
Error: Object not initialized
Error: Object not initialized
