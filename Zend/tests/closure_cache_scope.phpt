--TEST--
Zend: Stateless closure cache respects called_scope across subclasses
--FILE--
<?php
class ParentBase {
    public static function getScopeResolver(): Closure {
        return self::privResolver();
    }
    private static function privResolver(): Closure {
        return static fn() => static::class;
    }
}

class BranchA extends ParentBase {}
class BranchB extends ParentBase {}

$fnA = BranchA::getScopeResolver();
$resultA = $fnA();

$fnB = BranchB::getScopeResolver();
$resultB = $fnB();

var_dump($resultA === 'BranchA');
var_dump($resultB === 'BranchB');
?>
--EXPECT--
bool(true)
bool(true)
