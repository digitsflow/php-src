--TEST--
header() newline character safety checks in sapi_header_op()
--FILE--
<?php
header("X-Foo: bar\nX-Bar: baz");
echo "Done\n";
?>
--EXPECTF--
Warning: Header may not contain more than a single header, new line detected in %s on line %d
Done
