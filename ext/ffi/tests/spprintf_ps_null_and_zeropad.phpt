--TEST--
spprintf: %S, %pS, and %Z NULL pointer safety, zero-pad reset, and unsigned char ctype check (CVE-2026-8195)
--EXTENSIONS--
ffi
--SKIPIF--
<?php
require_once('utils.inc');
try {
    if (function_exists('ffi_cdef')) {
        ffi_cdef("extern void *zend_printf;", ffi_get_php_dll_name());
    } else {
        FFI::cdef("extern void *zend_printf;");
    }
} catch (Throwable $e) {
    die('skip PHP symbols not available');
}
?>
--INI--
ffi.enable=1
--FILE--
<?php
require_once('utils.inc');

$cdef = "extern size_t (*zend_printf)(const char *format, ...);";
$zend = function_exists('ffi_cdef')
    ? ffi_cdef($cdef, ffi_get_php_dll_name())
    : FFI::cdef($cdef);

// 1. CWE-476: NULL zend_string* / zval* passed to %S, %pS, and %Z must format as "(null)" instead of SIGSEGV
($zend->zend_printf)("null S: [%S]\n", null);
($zend->zend_printf)("null pS: [%pS]\n", null);
($zend->zend_printf)("null Z: [%Z]\n", null);

// 2. CWE-682: Preceding %04d numeric zero-padding must not leak pad_char='0' into subsequent %S, %pS, or %Z
($zend->zend_printf)("zeropad S: [%04d|%8S]\n", 42, null);
($zend->zend_printf)("zeropad pS: [%04d|%8pS]\n", 42, null);
($zend->zend_printf)("zeropad Z: [%04d|%8Z]\n", 42, null);

// 3. CWE-125 / STR37-C: High-bit byte following %p must not trigger negative signed char UB in isalnum()
ob_start();
($zend->zend_printf)("highbit p: [%p\xff]\n", null);
$out = ob_get_clean();
var_dump(str_starts_with($out, "highbit p: [0x0\xff]") || str_starts_with($out, "highbit p: [0\xff]"));
echo "OK\n";
?>
--EXPECT--
null S: [(null)]
null pS: [(null)]
null Z: [(null)]
zeropad S: [0042|  (null)]
zeropad pS: [0042|  (null)]
zeropad Z: [0042|  (null)]
bool(true)
OK
