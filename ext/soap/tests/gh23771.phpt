--TEST--
GH-23771 (ext/soap: safe integer handling and leak-free validation in to_xml_hexbin and to_zval_hexbin)
--EXTENSIONS--
soap
--FILE--
<?php
// Test 1: Valid hexBinary decoding via SoapServer
$xml_valid = '<?xml version="1.0" encoding="utf-8"?>
<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">
  <SOAP-ENV:Body>
    <testMethod><input xsi:type="xsd:hexBinary">48656c6c6f</input></testMethod>
  </SOAP-ENV:Body>
</SOAP-ENV:Envelope>';

class TestHandler {
    function testMethod($input) {
        var_dump($input);
        return $input;
    }
}

$server = new SoapServer(null, ['uri' => 'http://test/']);
$server->setClass('TestHandler');
$server->handle($xml_valid);

// Test 2: Invalid hexBinary decoding with odd length (lexical violation)
$xml_odd = '<?xml version="1.0" encoding="utf-8"?>
<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">
  <SOAP-ENV:Body>
    <testMethod><input xsi:type="xsd:hexBinary">48656c6c6</input></testMethod>
  </SOAP-ENV:Body>
</SOAP-ENV:Envelope>';

try {
    $server->handle($xml_odd);
} catch (SoapFault $e) {
    echo "Caught expected fault (odd length): " . $e->getMessage() . "\n";
}

// Test 3: Invalid hexBinary decoding with illegal hex character (must not leak memory)
$xml_invalid = '<?xml version="1.0" encoding="utf-8"?>
<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">
  <SOAP-ENV:Body>
    <testMethod><input xsi:type="xsd:hexBinary">48656c6cZZ</input></testMethod>
  </SOAP-ENV:Body>
</SOAP-ENV:Envelope>';

try {
    $server->handle($xml_invalid);
} catch (SoapFault $e) {
    echo "Caught expected fault (invalid char): " . $e->getMessage() . "\n";
}

echo "Done\n";
?>
--EXPECTF--
string(5) "Hello"
<?xml version="1.0" encoding="UTF-8"?>
<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns1="http://test/" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/" SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"><SOAP-ENV:Body><ns1:testMethodResponse><return xsi:type="xsd:string">Hello</return></ns1:testMethodResponse></SOAP-ENV:Body></SOAP-ENV:Envelope>
%aCaught expected fault (odd length): %s
%aCaught expected fault (invalid char): %s
Done
