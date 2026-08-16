--TEST--
IntlRuleBasedBreakIterator compiled rules outlive the source string
--EXTENSIONS--
intl
--SKIPIF--
<?php if (version_compare(INTL_ICU_VERSION, '68.1') < 0) die('skip for ICU >= 68.1'); ?>
--FILE--
<?php

$rules = <<<RULES
\$LN = [[:letter:] [:number:]];
\$S = [.;,:];

!!forward;
\$LN+ {1};
\$S+ {42};
!!reverse;
\$LN+ {1};
\$S+ {42};
!!safe_forward;
!!safe_reverse;
RULES;

$src = new IntlRuleBasedBreakIterator($rules);
$it = new IntlRuleBasedBreakIterator($src->getBinaryRules(), true);
unset($src);

$it->setText('ab,cd');
echo $it->first(), "\n";
while (true) {
    $n = $it->next();
    if ($n === IntlBreakIterator::DONE) {
        break;
    }
    echo $n, "\n";
}

$clone = clone $it;
$clone->setText('xy');
echo $clone->first(), "\n";
echo $clone->next(), "\n";

?>
--EXPECT--
0
2
3
5
0
2
