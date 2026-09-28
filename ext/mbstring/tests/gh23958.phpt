--TEST--
GH-23958 (Prefixes of encoding names are accepted as valid encodings)
--EXTENSIONS--
mbstring
--FILE--
<?php
foreach (["Windows", "UCS-4L", "UTF-8-M"] as $name) {
    try {
        mb_internal_encoding($name);
        echo "$name accepted as ", mb_internal_encoding(), PHP_EOL;
    } catch (ValueError $e) {
        echo $e::class, ": ", $e->getMessage(), PHP_EOL;
    }
}

try {
    mb_detect_order("aut");
    echo "aut accepted as ", implode(",", mb_detect_order()), PHP_EOL;
} catch (ValueError $e) {
    echo $e::class, ": ", $e->getMessage(), PHP_EOL;
}

foreach (["pas", ""] as $name) {
    try {
        mb_http_output($name);
        echo "\"$name\" accepted as ", mb_http_output(), PHP_EOL;
    } catch (ValueError $e) {
        echo $e::class, ": ", $e->getMessage(), PHP_EOL;
    }
}

var_dump(mb_internal_encoding("ucs-4le"), mb_detect_order("AUTO"), mb_http_output("pass"));
?>
--EXPECT--
ValueError: mb_internal_encoding(): Argument #1 ($encoding) must be a valid encoding, "Windows" given
ValueError: mb_internal_encoding(): Argument #1 ($encoding) must be a valid encoding, "UCS-4L" given
ValueError: mb_internal_encoding(): Argument #1 ($encoding) must be a valid encoding, "UTF-8-M" given
ValueError: mb_detect_order(): Argument #1 ($encoding) contains invalid encoding "aut"
ValueError: mb_http_output(): Argument #1 ($encoding) must be a valid encoding, "pas" given
ValueError: mb_http_output(): Argument #1 ($encoding) must be a valid encoding, "" given
bool(true)
bool(true)
bool(true)
