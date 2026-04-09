--TEST--
GH-21691 (OPcache CFG optimizer breaks reference returns with JMPZ)
--INI--
opcache.enable_cli=1
--EXTENSIONS--
opcache
--FILE--
<?php
class Base {
    protected function &getData(): array {
        $x = [];
        return $x;
    }

    public function process(): array {
        if ($data = &$this->getData() && !isset($data['key'])) {
        }
        return $data;
    }
}

class Child extends Base {
    protected function &getData(): array {
        static $x = ['value' => 42];
        return $x;
    }
}

$child = new Child();
var_dump($child->process());
?>
--EXPECT--
array(1) {
  ["value"]=>
  int(42)
}
