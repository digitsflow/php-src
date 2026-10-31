--TEST--
CVE-2026-8200: Reject embedded NUL bytes in stream_socket_sendto() and prevent bindto error_text leak
--FILE--
<?php
$udpSrv = stream_socket_server("udp://127.0.0.1:0", $errno, $errstr, STREAM_SERVER_BIND);
$udpName = stream_socket_get_name($udpSrv, false);
$udpPort = (int) substr($udpName, strrpos($udpName, ':') + 1);
stream_set_blocking($udpSrv, false);

$udpCli = stream_socket_client("udp://127.0.0.1:1");

/* Embedded NUL bytes in target address must be rejected, never NUL-truncated to 127.0.0.1:$udpPort */
var_dump(@stream_socket_sendto($udpCli, "injected1", 0, "127.0.0.1\0attacker.example:{$udpPort}"));
var_dump(@stream_socket_sendto($udpCli, "injected2", 0, "127.0.0.1:{$udpPort}\099999"));
var_dump(@stream_socket_sendto($udpCli, "injected3", 0, "[::1\0]:{$udpPort}"));

/* Verify zero datagrams reached the local UDP server */
$peer = null;
var_dump(stream_socket_recvfrom($udpSrv, 64, 0, $peer));

/* Valid target address still succeeds */
var_dump(stream_socket_sendto($udpCli, "valid", 0, "127.0.0.1:{$udpPort}") === 5);
var_dump(stream_socket_recvfrom($udpSrv, 64, 0, $peer));

/* Verify NUL-byte hostname without $errstr and invalid bindto options do not leak error_text */
$tcpSrv = stream_socket_server("tcp://127.0.0.1:0", $errno, $errstr);
$tcpAddr = stream_socket_get_name($tcpSrv, false);

var_dump(@stream_socket_client("tcp://127.0.0.1\0:80"));

foreach (["127.0.0.1\0:0", "127.0.0.1:99999", "0"] as $badBind) {
    $ctx = stream_context_create(['socket' => ['bindto' => $badBind]]);
    $fp = stream_socket_client("tcp://{$tcpAddr}", $errno, $errstr, 5, STREAM_CLIENT_CONNECT, $ctx);
    var_dump(is_resource($fp));
    if (is_resource($fp)) {
        fclose($fp);
    }
}

fclose($tcpSrv);
fclose($udpCli);
fclose($udpSrv);
?>
--EXPECT--
bool(false)
bool(false)
bool(false)
bool(false)
bool(true)
string(5) "valid"
bool(false)
bool(true)
bool(true)
bool(true)
