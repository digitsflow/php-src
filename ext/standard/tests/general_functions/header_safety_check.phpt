--TEST--
header() newline and NUL character safety checks in sapi_header_op()
--FILE--
<?php
header("X-Foo: bar\nX-Bar: baz");
header("X-Foo: bar\rX-Bar: baz");
header("X-Foo: bar\0baz");
echo "Done\n";
?>
--EXPECTF--
Warning: Header may not contain more than a single header, new line detected in %s on line %d

Warning: Header may not contain more than a single header, new line detected in %s on line %d

Warning: Header may not contain NUL bytes in %s on line %d
Done
