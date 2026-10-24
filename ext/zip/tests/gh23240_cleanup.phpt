--TEST--
GH-23240: extractTo() unlinks extracted files when CRC validation fails
--SKIPIF--
<?php if (!extension_loaded("zip")) print "skip zip extension not loaded"; ?>
--FILE--
<?php
$zip = new ZipArchive();
$archivePath = __DIR__ . '/test_crc_corrupt.zip';
$extractDir = __DIR__ . '/test_extract_target_' . uniqid();
mkdir($extractDir);

$zip->open($archivePath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
$zip->addFromString('payload.txt', str_repeat('X', 8192));
$zip->setCompressionName('payload.txt', ZipArchive::CM_STORE);
$zip->close();

$bin = file_get_contents($archivePath);
$bin = str_replace('XXXX', 'YYYY', $bin);
file_put_contents($archivePath, $bin);

$zip->open($archivePath);
$result = @$zip->extractTo($extractDir);
$zip->close();

var_dump($result);
var_dump(file_exists($extractDir . '/payload.txt'));
?>
--CLEAN--
<?php
@unlink(__DIR__ . '/test_crc_corrupt.zip');
foreach (glob(__DIR__ . '/test_extract_target_*') as $dir) {
    @unlink($dir . '/payload.txt');
    @rmdir($dir);
}
?>
--EXPECT--
bool(false)
bool(false)
