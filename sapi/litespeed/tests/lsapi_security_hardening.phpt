--TEST--
CVE-2026-8204: LiteSpeed SAPI v8.4 protocol, header, alignment, and prefork security hardening
--FILE--
<?php

$sapiRoot = dirname(PHP_BINARY, 2);
$lsphp = $sapiRoot . '/litespeed/lsphp';
if (!is_executable($lsphp)) {
    die("FAIL: expected executable lsphp at $lsphp\n");
}

$tmpBase = sys_get_temp_dir() . '/lsapi_sec_' . getmypid() . '_' . mt_rand(1000, 999999);
$sockPath = $tmpBase . '.sock';
$scriptPath = $tmpBase . '.php';
$secretPath = $tmpBase . '.secret';

file_put_contents($scriptPath, <<<'PHP'
<?php
@header("X-Bad-CRLF: bad\r\nX-Injected: pwn");
header("X-Good: clean-val");
$reqHeaders = function_exists('apache_request_headers') ? apache_request_headers() : [];
$respHeaders = function_exists('apache_response_headers') ? apache_response_headers() : [];
echo json_encode([
    'hyphen_getenv' => getenv('HTTP_X_CUSTOM_TEST'),
    'hyphen_server' => $_SERVER['HTTP_X_CUSTOM_TEST'] ?? null,
    'te_getenv' => getenv('HTTP_TRANSFER_ENCODING'),
    'te_server' => $_SERVER['HTTP_TRANSFER_ENCODING'] ?? null,
    'te_apache' => $reqHeaders['Transfer-Encoding'] ?? null,
    'content_length' => $_SERVER['CONTENT_LENGTH'] ?? 'unset',
    'resp_good' => $respHeaders['X-Good'] ?? null,
    'resp_bad' => $respHeaders['X-Bad-CRLF'] ?? null,
]);
PHP
);

function build_lsapi_request(string $scriptFile, int $reqBodyLen): string
{
    $isLittleEndian = (unpack('S', "\x01\x00")[1] === 1);
    $endianFlag = $isLittleEndian ? 0 : 1;

    $envs = [
        'SCRIPT_FILENAME' => $scriptFile,
        'SCRIPT_NAME' => '/lsapi_sec.php',
        'QUERY_STRING' => 'a=1',
        'REQUEST_METHOD' => 'GET',
        'SERVER_PROTOCOL' => 'HTTP/1.1',
        'DOCUMENT_ROOT' => dirname($scriptFile),
    ];

    // 44-byte lsapi_req_header placeholder + 4-byte empty special env terminator
    $pos = 44 + 4;
    $envBlob = '';
    $offsets = [];
    foreach ($envs as $k => $v) {
        $kLen = strlen($k) + 1;
        $vLen = strlen($v) + 1;
        $envBlob .= pack('nn', $kLen, $vLen) . $k . "\0";
        $pos += 4 + $kLen;
        $offsets[$k] = $pos;
        $envBlob .= $v . "\0";
        $pos += $vLen;
    }
    $envBlob .= "\0\0\0\0";
    $pos += 4;

    $padLen = (8 - ($pos % 8)) % 8;
    $padBlob = str_repeat("\0", $padLen);

    $httpHeader = "GET /lsapi_sec.php?a=1 HTTP/1.1\r\n"
        . "Host: localhost\r\n"
        . "Transfer-Encoding: chunked\r\n"
        . "X-Custom-Test: ok-hyphen\r\n\r\n\0";

    $headerLengths = array_fill(0, 25, 0);
    $headerOffsets = array_fill(0, 25, 0);

    // H_HOST = 10, H_TRANSFER_ENCODING = 24
    $headerOffsets[10] = strpos($httpHeader, 'localhost');
    $headerLengths[10] = strlen('localhost');
    $headerOffsets[24] = strpos($httpHeader, 'chunked');
    $headerLengths[24] = strlen('chunked');

    $headerIndexBlob = '';
    for ($i = 0; $i < 25; $i++) {
        $headerIndexBlob .= pack('S', $headerLengths[$i]);
    }
    $headerIndexBlob .= "\0\0"; // 4-byte alignment padding before int32_t m_headerOff[25]
    for ($i = 0; $i < 25; $i++) {
        $headerIndexBlob .= pack('l', $headerOffsets[$i]);
    }

    $unkNameOff = strpos($httpHeader, 'X-Custom-Test');
    $unkNameLen = strlen('X-Custom-Test');
    $unkValOff = strpos($httpHeader, 'ok-hyphen');
    $unkValLen = strlen('ok-hyphen');
    $unknownBlob = pack('llll', $unkNameOff, $unkNameLen, $unkValOff, $unkValLen);

    $bodyAfterHeader = "\0\0\0\0" . $envBlob . $padBlob . $headerIndexBlob . $unknownBlob . $httpHeader;
    $totalLen = 44 + strlen($bodyAfterHeader);

    $reqHeader = 'LS'
        . chr(1) // LSAPI_BEGIN_REQUEST
        . chr($endianFlag)
        . pack(
            'llllllllll',
            $totalLen,
            strlen($httpHeader),
            $reqBodyLen,
            $offsets['SCRIPT_FILENAME'],
            $offsets['SCRIPT_NAME'],
            $offsets['QUERY_STRING'],
            $offsets['REQUEST_METHOD'],
            1, // m_cntUnknownHeaders
            count($envs),
            0  // m_cntSpecialEnv
        );

    return $reqHeader . $bodyAfterHeader;
}

function send_and_collect_lsapi(string $sockPath, string $pkt): array
{
    $fp = false;
    for ($attempt = 0; $attempt < 25; $attempt++) {
        $fp = @stream_socket_client('unix://' . $sockPath, $errno, $errstr, 5.0);
        if ($fp !== false) {
            break;
        }
        usleep(20000);
    }
    if (!$fp) {
        return ['error' => "connect failed: $errstr ($errno)", 'headers' => [], 'body' => '', 'closed' => false];
    }
    stream_set_timeout($fp, 5);
    fwrite($fp, $pkt);

    $respHeaders = [];
    $respBody = '';
    $closedCleanly = false;

    while (!feof($fp)) {
        $hdr = '';
        while (strlen($hdr) < 8 && !feof($fp)) {
            $chunk = fread($fp, 8 - strlen($hdr));
            if ($chunk === '' || $chunk === false) {
                break;
            }
            $hdr .= $chunk;
        }
        if (strlen($hdr) < 8) {
            $closedCleanly = true;
            break;
        }
        if (substr($hdr, 0, 2) !== 'LS') {
            break;
        }
        $type = ord($hdr[2]);
        $pktLen = unpack('l', substr($hdr, 4, 4))[1];
        $payloadLen = $pktLen - 8;
        $payload = '';
        while (strlen($payload) < $payloadLen && !feof($fp)) {
            $chunk = fread($fp, $payloadLen - strlen($payload));
            if ($chunk === '' || $chunk === false) {
                break;
            }
            $payload .= $chunk;
        }
        if ($type === 3) { // LSAPI_RESP_HEADER
            $info = unpack('lcnt/lstatus', substr($payload, 0, 8));
            $cnt = $info['cnt'];
            $pos = 8 + 2 * $cnt;
            $rawHeaders = substr($payload, $pos);
            $respHeaders = array_values(array_filter(explode("\0", $rawHeaders), 'strlen'));
        } elseif ($type === 4) { // LSAPI_RESP_STREAM
            $respBody .= $payload;
        } elseif ($type === 5) { // LSAPI_RESP_END
            $closedCleanly = true;
            break;
        }
    }
    fclose($fp);
    return [
        'headers' => $respHeaders,
        'body' => $respBody,
        'closed' => $closedCleanly,
    ];
}

$env = getenv();
if (!is_array($env)) {
    $env = [];
}
$env['PHP_LSAPI_CHILDREN'] = '2';
$env['LSAPI_EXTRA_CHILDREN'] = '2';
$env['LSAPI_AVOID_FORK'] = '2G'; // Exercises 64-bit byte multiplication without signed int32 overflow
$env['PHP_LSAPI_MAX_REQUESTS'] = '10';
$env['LSAPI_PPID_NO_CHECK'] = '1';
if (function_exists('posix_geteuid') && posix_geteuid() === 0) {
    file_put_contents($secretPath, str_repeat('s', 32));
    chmod($secretPath, 0600);
    $env['LSAPI_SECRET'] = $secretPath;
}

$descriptors = [
    0 => ['file', '/dev/null', 'r'],
    1 => ['pipe', 'w'],
    2 => ['pipe', 'w'],
];
$proc = proc_open([$lsphp, '-n', '-b', $sockPath], $descriptors, $pipes, null, $env);
if (!is_resource($proc)) {
    die("FAIL: unable to start lsphp\n");
}
stream_set_blocking($pipes[1], false);
stream_set_blocking($pipes[2], false);

for ($i = 0; $i < 400 && !file_exists($sockPath); $i++) {
    usleep(20000);
}

// 1. Malformed negative m_reqBodyLen (< -2) must be rejected without executing the script
$badRes = send_and_collect_lsapi($sockPath, build_lsapi_request($scriptPath, -5));
var_dump($badRes['body'] === '');

// 2. Valid request exercises prefork child status mmap (64-bit s_avail_pages alignment),
//    fork hook php_child_init(), Transfer-Encoding (H_TRANSFER_ENCODING), hyphenated HTTP_*
//    lookup in GetHeaderVar(), and CRLF response header injection rejection.
$goodRes = send_and_collect_lsapi($sockPath, build_lsapi_request($scriptPath, 0));
$decoded = json_decode($goodRes['body'], true);

$hasGoodHeader = false;
$hasInjectedHeader = false;
foreach ($goodRes['headers'] as $h) {
    if ($h === 'X-Good: clean-val') {
        $hasGoodHeader = true;
    }
    if (stripos($h, 'X-Injected') !== false || stripos($h, 'X-Bad-CRLF') !== false) {
        $hasInjectedHeader = true;
    }
}

var_dump($hasGoodHeader);
var_dump($hasInjectedHeader);
var_dump($decoded['hyphen_getenv']);
var_dump($decoded['hyphen_server']);
var_dump($decoded['te_getenv']);
var_dump($decoded['te_server']);
var_dump($decoded['te_apache']);
var_dump($decoded['resp_good']);
var_dump($decoded['resp_bad']);

proc_terminate($proc, 15);
$stderr = stream_get_contents($pipes[2]);
fclose($pipes[1]);
fclose($pipes[2]);
proc_close($proc);

@unlink($sockPath);
@unlink($scriptPath);
@unlink($secretPath);

// Verify zero AddressSanitizer or UndefinedBehaviorSanitizer diagnostics on lsphp stderr
var_dump(stripos($stderr, 'Sanitizer') === false && stripos($stderr, 'runtime error:') === false);
?>
--EXPECT--
bool(true)
bool(true)
bool(false)
string(9) "ok-hyphen"
string(9) "ok-hyphen"
string(7) "chunked"
string(7) "chunked"
string(7) "chunked"
string(9) "clean-val"
NULL
bool(true)
