--TEST--
PDO: PDO::FETCH_BOTH row pre-allocation and column_count overflow safety (CVE-2026-8196)
--EXTENSIONS--
pdo
pdo_sqlite
--FILE--
<?php
$db = new PDO('sqlite::memory:');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->exec('CREATE TABLE test_fetch_both (id INTEGER PRIMARY KEY, name TEXT, score INTEGER, tag TEXT)');
$db->exec("INSERT INTO test_fetch_both VALUES (1, 'alpha', 100, 't1'), (2, 'beta', 200, 't2')");

$stmt = $db->query('SELECT id, name, score, tag FROM test_fetch_both ORDER BY id');
$row1 = $stmt->fetch(PDO::FETCH_BOTH);
var_dump(count($row1));
var_dump($row1[0] === $row1['id'] && $row1[1] === $row1['name'] && $row1[2] === $row1['score'] && $row1[3] === $row1['tag']);

$row2 = $stmt->fetch(PDO::FETCH_ASSOC);
var_dump(count($row2));

$stmtAll = $db->query('SELECT id, name, score, tag FROM test_fetch_both ORDER BY id');
$all = $stmtAll->fetchAll(PDO::FETCH_BOTH);
var_dump(count($all), count($all[0]), count($all[1]));
echo "OK\n";
?>
--EXPECT--
int(8)
bool(true)
int(4)
int(2)
int(8)
int(8)
OK
