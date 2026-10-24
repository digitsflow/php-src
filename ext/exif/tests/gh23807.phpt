--TEST--
GH-23807 (ext/exif: avoid overflow in exif_process_IFD_in_TIFF_impl())
--EXTENSIONS--
exif
--FILE--
<?php
// Construct a minimal TIFF with entry length exceeding INT32_MAX
// Header: II\x2a\x00, offset to IFD0 = 8
// IFD0: 1 entry
//   tag: 0x0100 (ImageWidth)
//   type: 0x0005 (URATIONAL, 8 bytes per value)
//   count: 0x40000000 (1073741824 items -> 8589934592 bytes > INT32_MAX)
//   offset: 0x00000008
// Next IFD: 0
$tiff = "II\x2a\x00\x08\x00\x00\x00" .
        "\x01\x00" .
        "\x00\x01\x05\x00\x00\x00\x00\x40\x08\x00\x00\x00" .
        "\x00\x00\x00\x00";

$tmp = tempnam(sys_get_temp_dir(), 'exif_test');
file_put_contents($tmp, $tiff);
var_dump(exif_read_data($tmp));
@unlink($tmp);
?>
--EXPECTF--
Warning: exif_read_data(%S): Read from TIFF: entry length of 8589934592 exceeds INT32_MAX in %s on line %d

Warning: exif_read_data(%S): Invalid TIFF file in %s on line %d
bool(false)
