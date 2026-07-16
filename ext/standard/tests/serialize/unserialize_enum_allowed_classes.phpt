--TEST--
unserialize() E: format must honor allowed_classes
--FILE--
<?php
enum AllowedEnum {
    case A;
}
enum DeniedEnum {
    case B;
}

$allowed = serialize(AllowedEnum::A);
$denied = serialize(DeniedEnum::B);

echo "false:\n";
var_dump(unserialize($denied, ["allowed_classes" => false]));
echo "empty list:\n";
var_dump(unserialize($denied, ["allowed_classes" => []]));
echo "whitelist other:\n";
var_dump(unserialize($denied, ["allowed_classes" => ["AllowedEnum"]]));
echo "whitelist matching:\n";
var_dump(unserialize($allowed, ["allowed_classes" => ["AllowedEnum"]]));
echo "true still works:\n";
var_dump(unserialize($denied, ["allowed_classes" => true]));
?>
--EXPECTF--
false:
object(__PHP_Incomplete_Class)#%d (1) {
  ["__PHP_Incomplete_Class_Name"]=>
  string(10) "DeniedEnum"
}
empty list:
object(__PHP_Incomplete_Class)#%d (1) {
  ["__PHP_Incomplete_Class_Name"]=>
  string(10) "DeniedEnum"
}
whitelist other:
object(__PHP_Incomplete_Class)#%d (1) {
  ["__PHP_Incomplete_Class_Name"]=>
  string(10) "DeniedEnum"
}
whitelist matching:
enum(AllowedEnum::A)
true still works:
enum(DeniedEnum::B)
