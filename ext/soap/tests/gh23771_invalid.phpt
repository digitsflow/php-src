--TEST--
GH-23771 (ext/soap: free allocated string before throwing encoding error on invalid hex character)
--EXTENSIONS--
soap
--FILE--
<?php
$xml_invalid = '<?xml version="1.0" encoding="utf-8"?>
<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">
  <SOAP-ENV:Body>
    <testMethod><input xsi:type="xsd:hexBinary">48656c6cZZ</input></testMethod>
  </SOAP-ENV:Body>
</SOAP-ENV:Envelope>';

$server = new SoapServer(null, ['uri' => 'http://test/']);
$server->handle($xml_invalid);
?>
--EXPECTF--
<?xml version="1.0" encoding="UTF-8"?>
<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/"><SOAP-ENV:Body><SOAP-ENV:Fault><faultcode>SOAP-ENV:Server</faultcode><faultstring>SOAP-ERROR: Encoding: Violation of encoding rules</faultstring></SOAP-ENV:Fault></SOAP-ENV:Body></SOAP-ENV:Envelope>
