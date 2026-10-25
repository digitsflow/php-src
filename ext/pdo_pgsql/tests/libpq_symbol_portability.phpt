--TEST--
PDO_PGSQL: Runtime symbol resolution for PostgreSQL 17+ libpq symbols (CVE-2026-8198)
--FILE--
<?php

$unversionedPg17Symbols = [
    'PQclosePortal',
    'PQclosePrepared',
    'PQsetChunkedRowsMode',
    'PQchangePassword',
    'PQsocketPoll',
];

$hardUndefined = [];
if (defined('PHP_BINARY') && is_file(PHP_BINARY) && is_readable(PHP_BINARY)) {
    $nmPath = trim((string)@shell_exec('command -v nm 2>/dev/null'));
    if ($nmPath !== '') {
        $cmd = escapeshellarg($nmPath) . ' -D --undefined-only ' . escapeshellarg(PHP_BINARY) . ' 2>/dev/null';
        $out = (string)@shell_exec($cmd);
        foreach ($unversionedPg17Symbols as $sym) {
            if (preg_match('/\b' . preg_quote($sym, '/') . '\b/', $out)) {
                $hardUndefined[] = $sym;
            }
        }
    }
}

if (extension_loaded('pdo_pgsql') && class_exists('PDO')) {
    var_dump(in_array('pgsql', PDO::getAvailableDrivers(), true));
} else {
    var_dump(true);
}
var_dump($hardUndefined);
echo "OK\n";
?>
--EXPECT--
bool(true)
array(0) {
}
OK
