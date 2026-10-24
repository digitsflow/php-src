--TEST--
<<<<<<< HEAD
GHSA-96wq-48vp-hh57: signed integer overflow of char array offset
--CREDITS--
Aleksey Solovev (Positive Technologies)
=======
GHSA-96wq-48vp-hh57: signed integer overflow of char array offset (CVE-2026-7568)
--CREDITS--
012git012
>>>>>>> 67324fe6d6c (fix(standard): prevent signed integer overflow of char array offset in metaphone() (GHSA-96wq-48vp-hh57, CVE-2026-7568))
--INI--
memory_limit=3G
--SKIPIF--
<?php
if (!getenv('RUN_RESOURCE_HEAVY_TESTS')) die('skip resource-heavy test');
if (getenv('SKIP_SLOW_TESTS')) die('skip slow test');
<<<<<<< HEAD
if (PHP_INT_SIZE != 8) die('skip 64-bit only');
if (PHP_DEBUG) die('skip Times out in debug builds');
=======
if (PHP_INT_SIZE != 8) echo 'skip 64-bit only';
>>>>>>> 67324fe6d6c (fix(standard): prevent signed integer overflow of char array offset in metaphone() (GHSA-96wq-48vp-hh57, CVE-2026-7568))
?>
--FILE--
<?php

$str = str_repeat('0', 2 * (1024 ** 3) - 2) . 'AE';
metaphone($str, 1);

?>
===DONE===
<<<<<<< HEAD
--EXPECTF--
Deprecated: Function metaphone() is deprecated since 8.6, use a userland phonetic matching library instead in %s on line %d
=======
--EXPECT--
>>>>>>> 67324fe6d6c (fix(standard): prevent signed integer overflow of char array offset in metaphone() (GHSA-96wq-48vp-hh57, CVE-2026-7568))
===DONE===
