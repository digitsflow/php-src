--TEST--
GH-23771 (ext/soap: safe integer handling and odd-length validation in to_zval_hexbin)
--EXTENSIONS--
soap
--FILE--
<?php
$xml_odd = '<?xml version="1.0" encoding="utf-8"?>
<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">
  <SOAP-ENV:Body>
    <testMethod><input xsi:type="xsd:hexBinary">48656c6c6</input></testMethod>
  </SOAP-ENV:Body>
</SOAP-ENV:Envelope>';

$server = new SoapServer(null, ['uri' => 'http://test/']);
$server->handle($xml_odd);
?>
--EXPECTF--
<?xml version="1.0" encoding="UTF-8"?>
<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/"><SOAP-ENV:Body><SOAP-ENV:Fault><faultcode>SOAP-ENV:Server</faultcode><faultstring>SOAP-ERROR: Encoding: Violation of encoding rules</faultstring></SOAP-ENV:Fault></SOAP-ENV:Body></SOAP-ENV:Envelope>
