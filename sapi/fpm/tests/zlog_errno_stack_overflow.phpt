--TEST--
CVE-2026-8199: FPM vzlog() ZLOG_SYSERROR stack buffer overflow when log_limit > MAX_BUF_LENGTH
--FILE--
<?php
$candidates = [
    dirname(PHP_BINARY, 2) . '/fpm/php-fpm',
    dirname(PHP_BINARY) . '/../sbin/php-fpm-digitsflow',
    dirname(PHP_BINARY) . '/../sbin/php-fpm',
];

$fpm = null;
foreach ($candidates as $c) {
    if (is_file($c) && is_executable($c)) {
        $fpm = $c;
        break;
    }
}
var_dump($fpm !== null);

$conf = __DIR__ . '/zlog_errno_stack_overflow.conf';

/* Sweep path lengths across the exact 2048-byte MAX_BUF_LENGTH boundary so
 * prefix + message lands in [2020..2047] (< buf_size) before appending
 * ": No such file or directory (2)" pushes len into [2051..2078] (> buf_size,
 * < log_limit=4096). */
$allOk = true;
for ($pad = 1970; $pad <= 2005; $pad += 5) {
    $badLog = '/nonexistent_zlog_cve2026_8199/' . str_repeat('a', $pad) . '.log';
    $ini = "[global]\n"
         . "log_limit = 4096\n"
         . "daemonize = no\n"
         . "error_log = {$badLog}\n"
         . "[www]\n"
         . "listen = 127.0.0.1:0\n"
         . "pm = static\n"
         . "pm.max_children = 1\n";
    file_put_contents($conf, $ini);

    $desc = [
        0 => ['pipe', 'r'],
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ];
    $proc = proc_open([$fpm, '-n', '-R', '-y', $conf, '-t'], $desc, $pipes);
    fclose($pipes[0]);
    $stdout = stream_get_contents($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    $code = proc_close($proc);

    /* Must exit cleanly (non-signal exit code > 0, < 128) with truncated line <= 2048 bytes */
    if ($code <= 0 || $code >= 128) {
        echo "Unexpected exit code {$code} at pad={$pad}: {$stderr}\n";
        $allOk = false;
        break;
    }
    foreach (explode("\n", trim($stderr)) as $line) {
        if (strlen($line) > 2048) {
            echo "Line exceeded MAX_BUF_LENGTH (" . strlen($line) . ") at pad={$pad}\n";
            $allOk = false;
            break 2;
        }
    }
    if (!str_contains($stderr, 'ERROR: failed to open error_log (')) {
        echo "Missing expected error_log diagnostic at pad={$pad}: {$stderr}\n";
        $allOk = false;
        break;
    }
}

@unlink($conf);
var_dump($allOk);
?>
--CLEAN--
<?php
@unlink(__DIR__ . '/zlog_errno_stack_overflow.conf');
?>
--EXPECT--
bool(true)
bool(true)
