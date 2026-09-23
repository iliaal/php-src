--TEST--
Test that password_verify() does not overread buffers when a short hash is passed
--FILE--
<?php
var_dump(password_verify("foo", '$2'));
var_dump(password_verify("foo", '$2y'));
var_dump(password_verify("foo", '$2y$'));
var_dump(password_verify("foo", '$2y$1'));
var_dump(password_verify("foo", '$2y$10'));
var_dump(password_verify("foo", '$2y$10$'));
var_dump(password_verify("rasmuslerdorf", '$2a$07$usesomesillystringfore2uDLvp1Ii2e./U9C8sBjqp8I90dH6hi'));
var_dump(password_verify("rasmuslerdorf", '$2b$07$usesomesillystringfore2uDLvp1Ii2e./U9C8sBjqp8I90dH6hi'));
var_dump(password_verify("rasmuslerdorf", '$2x$07$usesomesillystringfore2uDLvp1Ii2e./U9C8sBjqp8I90dH6hi'));
var_dump(password_verify("rasmuslerdorf", '$2y$07$usesomesillystringfore2uDLvp1Ii2e./U9C8sBjqp8I90dH6hi'));
var_dump(password_verify("rasmuslerdorf", "rl.3StKT.4T8M"));
?>
--EXPECT--
bool(false)
bool(false)
bool(false)
bool(false)
bool(false)
bool(false)
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
