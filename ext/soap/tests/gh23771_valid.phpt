--TEST--
GH-23771 (ext/soap: valid hexBinary roundtrip and encoding)
--EXTENSIONS--
soap
--FILE--
<?php
$val = new SoapVar("test string", XSD_HEXBINARY);
$param = new SoapParam($val, "test");
$client = new SoapClient(null, ['location' => 'http://localhost/', 'uri' => 'http://test/']);
var_dump($val->enc_type === XSD_HEXBINARY);
var_dump($val->enc_value === "test string");
echo "Done\n";
?>
--EXPECT--
bool(true)
bool(true)
Done
