--TEST--
SessionHandler: create_sid() properly throws when backend returns NULL
--SKIPIF--
<?php if (!extension_loaded("session")) print "skip session not loaded"; ?>
--FILE--
<?php
class CustomHandler extends SessionHandler {
    public function open(string $path, string $name): bool {
        return parent::open($path, $name);
    }
}

$handler = new CustomHandler();
session_set_save_handler($handler);
session_start();

var_dump(strlen($handler->create_sid()) > 0);
session_destroy();
?>
--EXPECT--
bool(true)
