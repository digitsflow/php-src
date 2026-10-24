--TEST--
metaphone() Lookahead boundary and signed integer safety (GHSA-96wq-48vp-hh57 / CVE-2026-7568)
--EXTENSIONS--
standard
--FILE--
<?php
error_reporting(E_ALL & ~E_DEPRECATED);

$words = [
    'AE',
    'GN',
    'KN',
    'PN',
    'WR',
    'X',
    'WH',
    'CIA',
    'TCH',
    'DGE',
    str_repeat('A', 100) . 'AE',
    str_repeat('C', 50) . 'H',
];

foreach ($words as $word) {
    var_dump(metaphone($word));
}
echo "===DONE===\n";
?>
--EXPECT--
string(1) "E"
string(1) "N"
string(1) "N"
string(1) "N"
string(1) "R"
string(1) "S"
string(1) "W"
string(1) "X"
string(1) "X"
string(1) "J"
string(1) "A"
string(50) "KKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKX"
===DONE===
