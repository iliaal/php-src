--TEST--
Zend Multibyte and declare(encoding=...) switching to an encoding of different width
--EXTENSIONS--
mbstring
tokenizer
--INI--
zend.multibyte=1
zend.script_encoding=ISO-8859-1
internal_encoding=UTF-8
--FILE--
<?php
function include_code(string $code) {
    static $n = 0;
    $file = __DIR__ . '/multibyte_encoding_010_' . ++$n . '.inc';
    file_put_contents($file, $code);
    return include $file;
}

var_dump(eval("/* \xC3\xA9\xC3\xA9 */ declare(encoding='ISO-8859-1'); return 'caf\xE9';"));
var_dump(eval("declare(encoding='ISO-8859-1'); return 'caf\xE9';"));
var_dump(eval("declare(encoding='UTF-32BE')" . mb_convert_encoding("; return 'ok';", 'UTF-32BE', 'UTF-8')));

$tokens = token_get_all("<?php /*" . str_repeat("\xC3\xA9", 200) . "*/ declare(encoding='ISO-8859-1'); echo 'caf\xE9';", TOKEN_PARSE);
echo implode('|', array_slice(array_map(fn($t) => is_array($t) ? $t[1] : $t, $tokens), -6)), PHP_EOL;

try {
    token_get_all('<?php' . str_repeat(' ', 300) . 'declare(encoding="UTF-32"); return 1;', TOKEN_PARSE);
} catch (ParseError $e) {
    echo $e::class, ": ", $e->getMessage(), PHP_EOL;
}

var_dump(include_code("<?php /* " . str_repeat("\xE9", 200) . " */ declare(encoding='UTF-8'); return 'caf\xC3\xA9';"));
var_dump(include_code("<?php /* \xA4\xA4 */ declare(encoding='ISO-8859-15'); return '\xA4';"));
var_dump(include_code("\xFF\xFE" . mb_convert_encoding("<?php declare(encoding='UTF-16BE')", 'UTF-16LE', 'UTF-8') . mb_convert_encoding("; return 'ok';", 'UTF-16BE', 'UTF-8')));
var_dump(include_code("\xFF\xFE" . mb_convert_encoding("<?php /* \u{E9}\u{E9} */ declare(encoding='UTF-8')", 'UTF-16LE', 'UTF-8') . "; return 'caf\xC3\xA9';"));

$code = "<?php /* \xE9\xE9\xE9 */ declare(encoding='UTF-8'); return __COMPILER_HALT_OFFSET__; __halt_compiler();data";
var_dump(include_code($code), strpos($code, 'data'));

mb_substitute_character(0xFFFD);
$code = mb_convert_encoding("<?php return __COMPILER_HALT_OFFSET__; __halt_compiler();", 'UTF-16LE', 'UTF-8') . "data";
var_dump(include_code($code), strpos($code, 'data'));

mb_substitute_character(0x1F600);
var_dump(eval("declare(encoding='UTF-16BE')" . mb_convert_encoding("; return 'ok';", 'UTF-16BE', 'UTF-8')));
?>
--CLEAN--
<?php
foreach (glob(__DIR__ . '/multibyte_encoding_010_*.inc') as $file) {
    unlink($file);
}
?>
--EXPECT--
string(5) "café"
string(5) "café"
string(2) "ok"
;| |echo| |'café'|;
ParseError: syntax error, unexpected token "??"
string(5) "café"
string(3) "€"
string(2) "ok"
string(5) "café"
int(94)
int(94)
int(114)
int(114)
string(2) "ok"
