--TEST--
SPL: SplFileInfo::getLinkTarget() with uninitialized filename and %pS exception formatting (CVE-2026-8195)
--FILE--
<?php
class UninitSplFileInfo extends SplFileInfo {
    public function __construct() {
        // Intentionally skip parent::__construct() so intern->file_name remains NULL
    }
}

$uninit = new UninitSplFileInfo();
try {
    $uninit->getLinkTarget();
} catch (Error $e) {
    echo "Uninit: " . $e->getMessage() . "\n";
}

$empty = new SplFileInfo("");
var_dump(@$empty->getLinkTarget());

$missing = new SplFileInfo(__DIR__ . "/nonexistent_symlink_target_test_8195");
try {
    @$missing->getLinkTarget();
} catch (RuntimeException $e) {
    echo "Missing: " . (str_contains($e->getMessage(), "Unable to read link") ? "OK" : $e->getMessage()) . "\n";
}
echo "DONE\n";
?>
--EXPECTF--
Uninit: %s
bool(false)
Missing: OK
DONE
