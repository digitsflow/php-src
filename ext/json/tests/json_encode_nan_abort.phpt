--TEST--
CVE-2026-8203: json_encode() aborts on INF/NAN without partial output and honors OOM under partial output
--FILE--
<?php

class SideEffectValue implements JsonSerializable
{
    public function jsonSerialize(): mixed
    {
        echo "UNEXPECTED jsonSerialize() call\n";
        return 'serialized';
    }
}

class SafeValue implements JsonSerializable
{
    public function jsonSerialize(): mixed
    {
        return 42;
    }
}

class DeclaredProps
{
    public $first;
    public $second;
    public $third;
}

$dynamic = new stdClass();
$dynamic->first = NAN;
$dynamic->second = new SideEffectValue();
$dynamic->third = "\xff";

$declared = new DeclaredProps();
$declared->first = INF;
$declared->second = new SideEffectValue();
$declared->third = "\xff";

$cases = [
    'array NAN before side-effect and UTF8' => [NAN, new SideEffectValue(), "\xff"],
    'array -INF before side-effect and UTF8' => [-INF, new SideEffectValue(), "\xff"],
    'dynamic object property NAN' => $dynamic,
    'declared object property INF' => $declared,
];

foreach ($cases as $label => $input) {
    echo $label, ":\n";
    var_dump(json_encode($input), json_last_error_msg());
}

echo "partial output preserves continuation:\n";
var_dump(
    json_encode([NAN, INF, -INF, new SafeValue()], JSON_PARTIAL_OUTPUT_ON_ERROR),
    json_last_error_msg()
);

// Verify delayed OOM under JSON_PARTIAL_OUTPUT_ON_ERROR | JSON_PRETTY_PRINT exits via Zend OOM (not SIGABRT hard_limit).
$code = <<<'PHP'
class RowObj {
    public $a;
    public $b;
}
$chunk = str_repeat('"', 512);
$arr = [];
for ($i = 0; $i < 6000; $i++) {
    $o = new RowObj();
    $o->a = $chunk;
    $o->b = $chunk;
    $arr[] = $o;
}
json_encode($arr, JSON_PARTIAL_OUTPUT_ON_ERROR | JSON_PRETTY_PRINT);
PHP;

$env = getenv();
if (!is_array($env)) {
    $env = [];
}
$env['USE_ZEND_ALLOC'] = '1';

$descriptors = [
    0 => ['pipe', 'r'],
    1 => ['pipe', 'w'],
    2 => ['pipe', 'w'],
];
$proc = proc_open([PHP_BINARY, '-n', '-d', 'memory_limit=8M', '-d', 'display_errors=1', '-r', $code], $descriptors, $pipes, null, $env);
fclose($pipes[0]);
$out = stream_get_contents($pipes[1]);
$err = stream_get_contents($pipes[2]);
fclose($pipes[1]);
fclose($pipes[2]);
$status = proc_close($proc);
$combined = $out . $err;
var_dump(strpos($combined, 'Allowed memory size of 8388608 bytes exhausted') !== false);
var_dump($status !== 134 && $status !== 6);
?>
--EXPECT--
array NAN before side-effect and UTF8:
bool(false)
string(34) "Inf and NaN cannot be JSON encoded"
array -INF before side-effect and UTF8:
bool(false)
string(34) "Inf and NaN cannot be JSON encoded"
dynamic object property NAN:
bool(false)
string(34) "Inf and NaN cannot be JSON encoded"
declared object property INF:
bool(false)
string(34) "Inf and NaN cannot be JSON encoded"
partial output preserves continuation:
string(10) "[0,0,0,42]"
string(34) "Inf and NaN cannot be JSON encoded"
bool(true)
bool(true)
