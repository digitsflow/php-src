--TEST--
JIT: AArch64 ir_patch 26-bit B sign-bit mask & unaligned size safety and trace patching (CVE-2026-8197)
--EXTENSIONS--
opcache
--INI--
opcache.enable=1
opcache.enable_cli=1
opcache.jit=tracing
opcache.jit_buffer_size=16M
opcache.jit_hot_loop=2
opcache.jit_hot_func=2
opcache.jit_hot_return=2
opcache.jit_hot_side_exit=2
ffi.enable=1
--FILE--
<?php
// 1. Exercise Tracing JIT trace linking & branch patching across hot loops and side exits
function jit_branch_patch_target(int $n): int {
    $acc = 0;
    for ($i = 0; $i < $n; $i++) {
        if (($i & 3) === 0) {
            $acc += $i * 2;
        } elseif (($i & 3) === 1) {
            $acc -= $i;
        } elseif (($i & 3) === 2) {
            $acc ^= ($i + 7);
        } else {
            $acc += 1;
        }
    }
    return $acc;
}

$results = [];
for ($r = 0; $r < 6; $r++) {
    $results[] = jit_branch_patch_target(64);
}
var_dump(count(array_unique($results)) === 1 && $results[0] === -64);

// 2. On AArch64 with FFI and exported ir_patch symbol, directly verify 26-bit B (imm26) mask & 4-byte size alignment
$arch = php_uname('m');
if (($arch === 'aarch64' || $arch === 'arm64') && extension_loaded('ffi')) {
    $cdef = "int ir_patch(const void *code, size_t size, uint32_t jmp_table_size, const void *from_addr, const void *to_addr);";
    $ffi = null;
    try {
        $ffi = FFI::cdef($cdef);
        // Verify symbol is resolvable in default namespace
        $tmp = $ffi->ir_patch;
    } catch (Throwable $e) {
        $ffi = null;
    }
    if ($ffi === null) {
        $so = rtrim((string)ini_get('extension_dir'), '/\\') . '/opcache.so';
        if (is_file($so)) {
            try {
                $ffi = FFI::cdef($cdef, $so);
            } catch (Throwable $e) {
                $ffi = null;
            }
        }
    }
    if ($ffi !== null) {
        try {
            // Allocate 16 uint32_t words so from_addr (+4 words) and to_addr (+8 words) are valid addresses
            $buf = FFI::new("uint32_t[16]");
            // Word 0: B +16 bytes (delta = +4 words -> imm26 = 0x00000004, bit 25 = 0) -> matches from_addr
            $buf[0] = 0x14000004;
            // Word 1: B (-128MiB + 12 bytes) (imm26 = 0x02000003, bit 25 = 1, lower 25 bits = 3 == delta to from_addr)
            // Pre-fix (& 0x01ffffffu) falsely matched Word 1 because bit 25 was masked out!
            $buf[1] = 0x16000003;

            $code_ptr  = FFI::addr($buf[0]);
            $from_addr = FFI::addr($buf[4]);
            $to_addr   = FFI::addr($buf[8]);

            // Pass size = 11 (unaligned to 4 bytes) to also verify p = end + (size & ~3) does not underflow end
            $patched = $ffi->ir_patch($code_ptr, 11, 0, $from_addr, $to_addr);
            if ($patched !== 1 || $buf[0] !== 0x14000008 || $buf[1] !== 0x16000003) {
                echo "FAIL: patched=$patched buf0=" . dechex($buf[0]) . " buf1=" . dechex($buf[1]) . "\n";
            }
        } catch (Throwable $e) {
            // Symbol not exported in static/stripped build
        }
    }
}
echo "OK\n";
?>
--EXPECT--
bool(true)
OK
