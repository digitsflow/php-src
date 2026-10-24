--TEST--
GH-24376 (Session file descriptor must have O_CLOEXEC set to prevent descriptor leaks)
--EXTENSIONS--
session
--SKIPIF--
<?php
if (substr(PHP_OS, 0, 3) == 'WIN') die('skip not for Windows');
if (!function_exists('proc_open')) die('skip proc_open required');
?>
--FILE--
<?php
$save_path = __DIR__ . '/session_cloexec_test_' . uniqid();
@mkdir($save_path);
ini_set('session.save_path', $save_path);
session_start();
$_SESSION['secret'] = 'digitsflow-session-token';

// In a child process spawned via proc_open, verify that the session file is not leaked as an open file descriptor
$php = PHP_BINARY;
$child_code = <<<'PHP'
$fds = @scandir("/proc/self/fd") ?: (@scandir("/dev/fd") ?: []);
$leaked = false;
foreach ($fds as $fd) {
    if ($fd === '.' || $fd === '..') continue;
    $target = @readlink("/proc/self/fd/$fd") ?: @readlink("/dev/fd/$fd");
    if ($target && str_contains($target, 'sess_')) {
        $leaked = true;
        break;
    }
}
echo $leaked ? "LEAKED\n" : "OK\n";
PHP;

$descriptors = [
    0 => ['pipe', 'r'],
    1 => ['pipe', 'w'],
    2 => ['pipe', 'w'],
];

$proc = proc_open([$php, '-r', $child_code], $descriptors, $pipes);
if (is_resource($proc)) {
    echo stream_get_contents($pipes[1]);
    fclose($pipes[0]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    proc_close($proc);
} else {
    echo "OK\n";
}

session_destroy();
@unlink($save_path . '/sess_' . session_id());
@rmdir($save_path);
?>
--EXPECT--
OK
