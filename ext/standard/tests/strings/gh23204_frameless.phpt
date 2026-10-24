--TEST--
Fix GH-23204: implode() with object in array mutating glue
--FILE--
<?php
class Mutator {
    public function __toString() {
        global $glue;
        unset($glue);
        return "mutated";
    }
}

$glue = str_repeat(":", 16);
$arr = ["a", new Mutator(), "b"];

$res = implode($glue, $arr);
echo $res . "\n";
?>
--EXPECT--
a::::::::::::::::mutated::::::::::::::::b
