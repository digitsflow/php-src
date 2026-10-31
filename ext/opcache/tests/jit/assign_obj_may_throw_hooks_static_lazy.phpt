--TEST--
CVE-2026-8201: ZEND_ASSIGN_OBJ zend_may_throw must guard op1_use == -1 ($this), CV0, static, and readonly properties
--EXTENSIONS--
opcache
--INI--
opcache.enable=1
opcache.enable_cli=1
opcache.jit=1255
opcache.jit_buffer_size=32M
opcache.optimization_level=-1
--FILE--
<?php
set_error_handler(function (int $errno, string $errstr): bool {
    throw new RuntimeException("ERR:$errstr");
});

final class StaticPropBox {
    public static $s = 0;

    public function assignThisStatic(): string {
        /* $this->s = 1 has op1_type == IS_UNUSED and ssa_op->op1_use == -1 */
        $marker = "before";
        try {
            $this->s = 1;
            $marker = "unreached";
        } catch (Throwable $e) {
            return "$marker:" . $e->getMessage();
        }
        return $marker;
    }
}

final class ReadonlyBox {
    public readonly int $r;

    public function init(int $v): void {
        $this->r = $v;
    }
}

function assignStaticViaLocal(): string {
    /* $box is CV0 (ssa_op->op1_use == 0 on first use) */
    $box = new StaticPropBox();
    $state = 10;
    try {
        $box->s = 42;
        $state = 99;
    } catch (Throwable $e) {
        return "$state:" . $e->getMessage();
    }
    return "bad:$state";
}

function assignReadonlyTwice(ReadonlyBox $box): string {
    $state = 20;
    try {
        $box->r = 2;
        $state = 99;
    } catch (Throwable $e) {
        return "$state:" . $e->getMessage();
    }
    return "bad:$state";
}

$sb = new StaticPropBox();
$rb = new ReadonlyBox();
$rb->init(1);

for ($i = 0; $i < 20; $i++) {
    $r1 = $sb->assignThisStatic();
    $r2 = assignStaticViaLocal();
    $r3 = assignReadonlyTwice($rb);
}

var_dump(str_starts_with($r1, "before:ERR:Accessing static property StaticPropBox::\$s as non static"));
var_dump(str_starts_with($r2, "10:ERR:Accessing static property StaticPropBox::\$s as non static"));
var_dump($r3 === "20:Cannot modify readonly property ReadonlyBox::\$r");
?>
--EXPECT--
bool(true)
bool(true)
bool(true)
