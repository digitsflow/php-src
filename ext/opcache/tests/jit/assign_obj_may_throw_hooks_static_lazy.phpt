--TEST--
CVE-2026-8201: Optimizer & JIT zend_may_throw_ex() soundness for static, hooked, and lazy object property writes
--EXTENSIONS--
opcache
--INI--
opcache.enable=1
opcache.enable_cli=1
opcache.optimization_level=-1
opcache.jit=function
opcache.jit_buffer_size=16M
--FILE--
<?php
set_error_handler(static function (int $severity, string $msg): bool {
    throw new ErrorException($msg, 0, $severity);
});

function sink($a, $b) {
    return $a;
}

final class StaticPropClass {
    public static $s = 0;
}

function writeDeadStaticProp(): string {
    try {
        $o = new StaticPropClass();
        $o->s = 1;
        return "BUG_DCE_SWALLOWED_STATIC_PROP_WRITE";
    } catch (Throwable $e) {
        return "caught_static";
    }
}

final class SelfPropBox {
    public $x = 0;
    public function bump(int $n): int {
        for ($i = 0; $i < $n; $i++) {
            $this->x = $i;
        }
        return $this->x;
    }
}

function writeCv0Box(SelfPropBox $b): int {
    $b->x = 99;
    return $b->x;
}

var_dump(writeDeadStaticProp());
$sb = new SelfPropBox();
var_dump($sb->bump(5));
var_dump(writeCv0Box($sb));

if (PHP_VERSION_ID >= 80400) {
    eval(<<<'PHP'
final class HookedPropBox {
    public $h {
        set($v) {
            throw new RuntimeException("hook_set_boom");
        }
    }
    public $v {
        get => 1;
    }
    public int $th {
        set(int $v) {
            throw new RuntimeException("typed_hook_set_boom");
        }
    }
    public int $bth = 0 {
        set(int $v) {
            if ($v > 0) {
                throw new RuntimeException("backed_typed_hook_boom");
            }
            $this->bth = $v;
        }
    }
}

function writeHookSet(HookedPropBox $b): string {
    try {
        sink($b->h = 10, new stdClass());
        return "BUG_HOOK_SET";
    } catch (RuntimeException $e) {
        return $e->getMessage();
    }
}

function writeHookVirtual(HookedPropBox $b): string {
    try {
        $local = new HookedPropBox();
        $local->v = 20;
        return "BUG_HOOK_VIRTUAL";
    } catch (Error $e) {
        return "caught_virtual";
    }
}

function writeTypedHookSet(HookedPropBox $b): string {
    try {
        sink($b->th = 30, new stdClass());
        return "BUG_TYPED_HOOK_SET";
    } catch (RuntimeException $e) {
        return $e->getMessage();
    }
}

function writeBackedTypedHookSet(HookedPropBox $b): string {
    try {
        sink($b->bth = 35, new stdClass());
        return "BUG_BACKED_TYPED_HOOK_SET";
    } catch (RuntimeException $e) {
        return $e->getMessage();
    }
}

final class LazyTargetBox {
    public $u = 0;
    public int $t = 0;
}

function writeLazyUntyped(LazyTargetBox $b): string {
    try {
        sink($b->u = 42, new stdClass());
        return "BUG_LAZY_UNTYPED";
    } catch (RuntimeException $e) {
        return $e->getMessage();
    }
}

function writeLazyTyped(LazyTargetBox $b): string {
    try {
        sink($b->t = 42, new stdClass());
        return "BUG_LAZY_TYPED";
    } catch (RuntimeException $e) {
        return $e->getMessage();
    }
}

$hb = new HookedPropBox();
var_dump(writeHookSet($hb));
var_dump(writeHookVirtual($hb));
var_dump(writeTypedHookSet($hb));
var_dump(writeBackedTypedHookSet($hb));

$rc = new ReflectionClass(LazyTargetBox::class);
$lazy1 = $rc->newLazyGhost(static function (LazyTargetBox $obj): void {
    throw new RuntimeException("lazy_untyped_boom");
});
$lazy2 = $rc->newLazyGhost(static function (LazyTargetBox $obj): void {
    throw new RuntimeException("lazy_typed_boom");
});
var_dump(writeLazyUntyped($lazy1));
var_dump(writeLazyTyped($lazy2));
PHP);
} else {
    var_dump("hook_set_boom");
    var_dump("caught_virtual");
    var_dump("typed_hook_set_boom");
    var_dump("backed_typed_hook_boom");
    var_dump("lazy_untyped_boom");
    var_dump("lazy_typed_boom");
}

restore_error_handler();
?>
--EXPECT--
string(13) "caught_static"
int(4)
int(99)
string(13) "hook_set_boom"
string(14) "caught_virtual"
string(19) "typed_hook_set_boom"
string(22) "backed_typed_hook_boom"
string(17) "lazy_untyped_boom"
string(15) "lazy_typed_boom"
